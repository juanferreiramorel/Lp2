
create table cuentas_a_pagar( 
	id_cta serial,
	id_proveedor integer not null, 
	id_compra integer not null, 
	vencimiento date not null,
	importe float not null,
	nro_cuota integer not null,
	estado varchar(20) default 'PENDIENTE',
	primary key(id_cta),
)

-- Función
CREATE OR REPLACE FUNCTION public.generar_ctas_pagar()
RETURNS trigger AS
$BODY$
DECLARE
    v_monto_cuota   integer;
    v_vencimiento   date;
    v_cont          integer;
    v_dias          interval;
    v_primer_vto    date;
    v_cuotas        integer;
    v_intervalo     integer;
BEGIN
    IF NEW.condicion_compra = 'CREDITO' THEN
        v_cuotas    := COALESCE(NEW.cantidad_cuotas, 1);
        IF v_cuotas <= 0 THEN v_cuotas := 1; END IF;

        v_intervalo := COALESCE(NEW.intervalo, 30);
        v_dias      := (v_intervalo::text || ' days')::interval;

        v_monto_cuota := ROUND(NEW.total / NEW.cantidad_cuotas);
        v_primer_vto  := NEW.fecha_compra + v_dias;

        FOR v_cont IN 1..v_cuotas LOOP
            v_vencimiento := v_primer_vto + ((v_cont - 1) * v_dias);

            INSERT INTO public.cuentas_a_pagar(
                id_proveedor, id_compra, vencimiento, importe, nro_cuota, estado
            ) VALUES (
                NEW.id_proveedor, NEW.id_compra, v_vencimiento, v_monto_cuota, v_cont, 'PENDIENTE'
            );
        END LOOP;
    END IF;

    RETURN NEW;
END;
$BODY$
LANGUAGE plpgsql;

-- Trigger
DROP TRIGGER IF EXISTS trg_ctas_a_pagar ON public.compras;

CREATE TRIGGER trg_ctas_a_pagar
AFTER INSERT ON public.compras
FOR EACH ROW
EXECUTE PROCEDURE public.generar_ctas_pagar();
