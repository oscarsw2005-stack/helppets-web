# helppets-web
Aplicativo web de veterinaria.

## Inicio de sesión y registro

1. Importa `base_datos/veterinaria.sql` en MySQL.
2. Configura las credenciales de la base de datos en `conexion_bd.php`.
3. Sirve el proyecto con PHP y la extensión `mysqli` habilitada; abre `index.html`.
4. El acceso se valida por documento contra `usuario`. Las cuentas con rol `Cliente` llegan a `panel_cliente.php`; si el documento no existe, el acceso ofrece ir al registro.
5. El registro crea la cuenta y su perfil de cliente en una transacción. Las nuevas contraseñas se guardan con hash.
