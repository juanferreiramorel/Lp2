-- columnas agregadas a la tabla clientes
alter table clientes add column clie_fecha_nac date;
alter table clientes add column id_departamento integer;
-- agregar clave foreanea
alter table clientes add constraint fk_clientes_departamento 
foreign key(id_departamento) 
references departamentos(id_departamento);