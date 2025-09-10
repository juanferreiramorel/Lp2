create table pedidos(
    id_pedido serial not null,
    id_cliente integer not null,
    fecha_pedido date not null,
    total_pedido decimal(10, 2) not null,
    id_sucursal integer,
    id_usuario integer,
    estado varchar(20) not null default 'PENDIENTE',
    primary key (id_pedido),
    foreign key (id_cliente) references clientes(id_cliente),
    foreign key (id_sucursal) references sucursales(id_sucursal),
    foreign key (id_usuario) references users(id)
)

create table detalle_pedido(
    id_detalle_pedido serial not null,
    id_pedido integer not null,
    id_producto integer not null,
    cantidad integer not null,
    precio_unitario decimal(10, 2) not null,
    primary key (id_detalle_pedido),
    foreign key (id_pedido) references pedidos(id_pedido),
    foreign key (id_producto) references productos(id_producto)
)

-- crear la columna en la tabla de ventas
alter table ventas add column id_pedido integer;
alter table ventas add foreign key (id_pedido) references pedidos(id_pedido);

-- crear la columna en la tabla de users
alter table pedidos add column id_usuario integer;
alter table pedidos add foreign key (id_usuario) references users(id);
-- crear la columna sucursales en la tabla de compras
alter table compras add column id_sucursal integer;
alter table compras add foreign key (id_sucursal) references sucursales(id_sucursal);
-- crear la columna condicion_compra en la tabla de compras
alter table compras add column condicion_compra varchar(20) not null default 'CONTADO';
-- crear la columna intervalo en la tabla de compras
alter table compras add column intervalo integer;
-- crear la columna factura en la tabla de compras
alter table compras add column factura varchar(20) not null;
-- crear la columna cantidad_cuotas en la tabla de compras
alter table compras add column cantidad_cuotas integer;
-- crear la columna estado en la tabla de compras
alter table compras add column estado varchar(20) not null default 'COMPLETADO';

-- Crear los formulario cabecera y detalle similar a ventas
1. php artisan make:controller PedidosController
2. Crear la ruta en routes/web.php
3. Crear las vistas necesarias en resources/views/pedidos con el comando php artisan infyom.scaffold:views Pedidos --fromTable --table=pedidos
4. Crear el menu en resources/views/layouts/menu.blade.php
5. Realizar la programacion en el controlador app/Http/Controllers/PedidosController.php