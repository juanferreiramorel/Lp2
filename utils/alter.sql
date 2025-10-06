--agregar colummna imagen a productos
ALTER TABLE productos ADD COLUMN imagen_producto VARCHAR(199);


-- agregar columman sucursal_id en users
ALTER TABLE users ADD COLUMN id_sucursal INTEGER;
ALTER TABLE users ADD CONSTRAINT fk_sucursal_users FOREIGN KEY (id_sucursal) REFERENCES sucursales(id_sucursal);