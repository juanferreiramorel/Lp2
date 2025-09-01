-- alter ventas
alter table ventas add column condicion_venta varchar(50);
alter table ventas add column intervalo integer default 0;
COMMENT ON column ventas.intervalo 
is 'intervalo para saber cada cuanto sera el vencimiento solo para creditos';
alter table ventas add column cantidad_cuota integer default 0;
ALTER TABLE ventas ADD COLUMN estado varchar(30);

-- crear columna sucursales en ventas
alter table ventas add column id_sucursal integer
-- agregar foreign en ventas
alter table ventas add constraint  fk_id_sucursal
foreign key(id_sucursal) references sucursales(id_sucursal);
 