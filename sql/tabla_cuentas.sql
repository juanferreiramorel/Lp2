create table cuentas_a_cobrar(
	id_cta serial,
	id_cliente integer not null,
	id_venta integer not null,
	vencimiento date not null,
	importe float not null,
	nro_cuota integer not null,
	estado varchar(20) default 'PENDIENTE',
	primary key(id_cta),
	foreign key(id_cliente) references clientes(id_cliente),
	foreign key (id_venta) references ventas(id_venta)
)