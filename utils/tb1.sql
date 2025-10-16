// Auditoría de cambios en tablas (inserciones, actualizaciones, borrados)
CREATE SCHEMA IF NOT EXISTS audit;

CREATE TABLE IF NOT EXISTS audit.log (
  id           bigserial PRIMARY KEY,
  schema_name  text        NOT NULL,
  table_name   text        NOT NULL,
  operation    char(20)     NOT NULL,              -- 'AGREGA'|'MODIFICA'|'ELIMINA'
  changed_at   timestamptz NOT NULL DEFAULT now(),
  user_db      name        NOT NULL,              -- usuario DB
  user_id      integer,                           -- <-- id de tu tabla users
  client_addr  inet,
  txid         bigint      NOT NULL DEFAULT txid_current(),
  old_data     jsonb,
  new_data     jsonb
);

CREATE INDEX IF NOT EXISTS ix_audit_log_when   ON audit.log (changed_at);
CREATE INDEX IF NOT EXISTS ix_audit_log_tblop  ON audit.log (schema_name, table_name, operation);
CREATE INDEX IF NOT EXISTS ix_audit_log_userid ON audit.log (user_id);

ALTER TABLE audit.log
  ADD CONSTRAINT fk_audit_log_user
  FOREIGN KEY (user_id)
  REFERENCES public.users(id)
  ON UPDATE RESTRICT
  ON DELETE SET NULL
  DEFERRABLE INITIALLY DEFERRED
  NOT VALID;

// Función genérica para triggers de auditoría
CREATE OR REPLACE FUNCTION audit.if_modified_func()
RETURNS trigger
LANGUAGE plpgsql
AS $$
DECLARE
  v_user_id_text text;
  v_user_id      integer;
BEGIN
  -- En PG 9.5 current_setting() SIN segundo parámetro:
  -- si la GUC no existe, lanza 'undefined_object' => la capturamos.
  BEGIN
    v_user_id_text := current_setting('app.user_id');
  EXCEPTION WHEN undefined_object THEN
    v_user_id_text := NULL;
  END;

  IF v_user_id_text IS NOT NULL AND length(trim(v_user_id_text)) > 0 THEN
    v_user_id := trim(v_user_id_text)::integer;
  ELSE
    v_user_id := NULL;
  END IF;

  IF TG_TABLE_SCHEMA = 'audit' THEN
    IF TG_OP = 'DELETE' THEN RETURN OLD; ELSE RETURN NEW; END IF;
  END IF;

  IF TG_OP = 'INSERT' THEN
    INSERT INTO audit.log(schema_name, table_name, operation, user_db, user_id, client_addr, new_data)
    VALUES (TG_TABLE_SCHEMA, TG_TABLE_NAME, 'AGREGA', session_user, v_user_id, inet_client_addr(), to_jsonb(NEW));
    RETURN NEW;

  ELSIF TG_OP = 'UPDATE' THEN
    INSERT INTO audit.log(schema_name, table_name, operation, user_db, user_id, client_addr, old_data, new_data)
    VALUES (TG_TABLE_SCHEMA, TG_TABLE_NAME, 'MODIFICA', session_user, v_user_id, inet_client_addr(), to_jsonb(OLD), to_jsonb(NEW));
    RETURN NEW;

  ELSIF TG_OP = 'DELETE' THEN
    INSERT INTO audit.log(schema_name, table_name, operation, user_db, user_id, client_addr, old_data)
    VALUES (TG_TABLE_SCHEMA, TG_TABLE_NAME, 'ELIMINA', session_user, v_user_id, inet_client_addr(), to_jsonb(OLD));
    RETURN OLD;
  END IF;

  RETURN NULL;
END;
$$;

// Creación de triggers de auditoría
DO $$
DECLARE
  r record;
  trg_name text;
BEGIN
  FOR r IN
    SELECT schemaname, tablename
    FROM pg_catalog.pg_tables
    WHERE schemaname NOT IN ('pg_catalog','information_schema','audit')
  LOOP
    trg_name := format('trg_audit_%I', r.tablename);

    EXECUTE format('DROP TRIGGER IF EXISTS %I ON %I.%I',
                   trg_name, r.schemaname, r.tablename);

    EXECUTE format(
      'CREATE TRIGGER %I
         AFTER INSERT OR UPDATE OR DELETE
         ON %I.%I
       FOR EACH ROW
       EXECUTE PROCEDURE audit.if_modified_func()',
      trg_name, r.schemaname, r.tablename
    );
  END LOOP;
END
$$;
