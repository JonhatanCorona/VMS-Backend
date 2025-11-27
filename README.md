# 📦 Sistema de Gestión de Almacén (WMS) - Backend PHP

Este proyecto implementa la capa de backend para un Sistema de Gestión de Almacén (Warehouse Management System o WMS), enfocándose en la seguridad, la autenticación basada en roles y la restricción de datos por almacén asignado al usuario.

El backend está desarrollado en **PHP** y utiliza la extensión **PDO** para una gestión segura de la base de datos MySQL.

---

## 🚀 Despliegue y URL Base

El servicio está desplegado y accesible en la siguiente URL base:  
[https://vms-backend-s4sj.onrender.com]


## Configuración del archivo `.env`

La aplicación utiliza un archivo `.env` para gestionar las variables de entorno, como la conexión a la base de datos. Esto permite mantener datos sensibles fuera del código fuente.

### Ejemplo de configuración de la base de datos:
MYSQL_URL=mysql://<USUARIO>:<CONTRASEÑA>@<HOST>:<PUERTO>/<NOMBRE_BD>


#### Descripción de cada parte:

- `mysql://` → Indica que se trata de una base de datos MySQL.  
- `<USUARIO>` → Usuario de la base de datos.  
- `<CONTRASEÑA>` → Contraseña del usuario.  
- `<HOST>` → Host o dirección del servidor de base de datos.  
- `<PUERTO>` → Puerto de conexión.  
- `<NOMBRE_BD>` → Nombre de la base de datos a la que se conectará la aplicación.

### Cómo usarlo en la aplicación

1. Crear un archivo `.env` en la raíz del proyecto.  
2. Copiar la variable `MYSQL_URL` con los datos correspondientes a tu entorno.  
3. La aplicación leerá automáticamente esta variable para conectarse a la base de datos mediante `config/database.php`, Otra forma de que se conecte localmente es colocar la URL directamente en `config/database.php`, solo para pruebas no es recomendable para produccion.



---

## 🏗️ Estructura del Proyecto

La aplicación sigue una estructura de directorios modular:

├── auth/

│ ├── login.php # Endpoint POST /auth/login (Inicio de Sesión)

│ └── me.php # Endpoint GET /auth/me (Verificación de Sesión)

├── config/

│ └── database.php # Clase para la conexión a la BD (PDO)

├── sql/
│ └── schema.sql # Script SQL de creación de tablas

├── utils/

│ ├── helpers.php # Funciones de utilidad (JSON, Token Bearer, etc.)

│ └── crear_usuario.php # Script de utilidad para insertar un usuario de prueba (Seeder)

└── Dockerfile # Configuración para el despliegue en contenedores


---

## 🔑 Parte 1: Autenticación y Seguridad

La autenticación se basa en la generación y validación de un **Token Bearer**, el cual se asocia a un usuario y su `almacen_id`.

| Archivo | Ruta | Método | Descripción |
|--------|------|--------|------------|
| auth/login.php | /auth/login | POST | Valida credenciales, verifica la asignación de `almacen_id`, y genera un token de sesión (`t_tokens`). |
| auth/me.php | /auth/me | GET | Valida el token Bearer y devuelve la información completa del usuario logueado. |
| config/database.php | (Clase) | N/A | Clase `Database` que gestiona la conexión a la BD usando variable de entorno de la base de datos (`MYSQL_URL`). |
| utils/helpers.php | (Funciones) | N/A | Provee `json_input`, `send_json`, y `get_bearer_token`. |

### A. Endpoint: Inicio de Sesión (POST /auth/login)

**Objetivo:** Obtener un token de acceso para las peticiones subsiguientes.

**Petición de Ejemplo (Postman o Thunder Client):**

```bash
1️⃣ Configuración general

Método: POST

URL: https://vms-backend-s4sj.onrender.com/auth/login

Headers:

Content-Type: application/json


Body: (tipo JSON)

{
  "email": "operador@otp.com",
  "password": "123456"
}
```

Respuesta Exitosa (200 OK):
```bash
{
  "status": "success",
  "user": {
    "id": 1,
    "nombre": "Carlos Pérez",
    "rol": "operador",
    "almacen_id": 2
  },
  "token": "abc123..."
}
```

Respuesta de Error (401 Unauthorized):
```bash
{
  "status": "error",
  "message": "Credenciales inválidas."
}
```
### B. Endpoint: Verificar Sesión (GET /auth/me)

Objetivo: Validar un token de sesión y recuperar los datos del usuario.

**Petición de Ejemplo (Postman o Thunder Client):**
```bash
1️⃣ Configuración general

Método: GET

URL: https://vms-backend-s4sj.onrender.com/auth/me

Headers:

Authorization: Bearer <TOKEN_GENERADO_PREVIAMENTE>


⚠️ Reemplaza <TOKEN_GENERADO_PREVIAMENTE> por el token que obtuviste en la petición de login.
```

Respuesta Exitosa (200 OK):
```bash
{
  "status": "success",
  "user": {
    "id": 1,
    "nombre": "Carlos Pérez",
    "email": "operador@otp.com",
    "rol": "operador",
    "almacen_id": 2
  }
}
```


## 📊 Parte 2: Esquema SQL para WMS

### Tablas Requeridas

Las siguientes tablas se definen en sql/schema.sql y son la base de datos del WMS, incluyendo la relación clave entre usuarios y almacenes.

### 🔍 Tarea SQL (Tracking Real de Paquetes)

A continuación, se presenta la consulta SQL que simula el seguimiento logístico, devolviendo los últimos 5 movimientos de un paquete específico, filtrando por el almacén del usuario para asegurar la seguridad y el alcance correcto de los datos.

Consulta SQL (Últimos 5 Movimientos con Seguridad de Almacén)
Insertar datos de prueba:

**-- Insertar paquetes**
```bash
INSERT INTO t_paquete (tracking, descripcion, peso, estado_actual, almacen_id) VALUES
('PKG001', 'Paquete de prueba 1', 2.5, 'INGRESO', 2),
('PKG002', 'Paquete de prueba 2', 1.2, 'PICKING', 2);
```

**-- Insertar movimientos**
```bash
INSERT INTO t_tracking (paquete_id, usuario_id, tipo_movimiento, descripcion) VALUES
(1, 1, 'INGRESO', 'Paquete recibido en almacén'),
(1, 1, 'PICKING', 'Paquete preparado para salida'),
(2, 1, 'INGRESO', 'Paquete recibido en almacén');
```


Probar la consulta de nuevo, reemplazando @usuario_id y @paquete_tracking por valores reales:
```bash
SET @usuario_id = 1;
SET @paquete_tracking = 'PKG001';

SELECT 
    t.fecha,
    t.tipo_movimiento,
    t.descripcion AS movimiento_descripcion,
    u.nombre AS usuario,
    p.tracking,
    p.estado_actual
FROM t_tracking t
JOIN t_paquete p ON t.paquete_id = p.id
JOIN t_usuarios u ON t.usuario_id = u.id
JOIN t_usuarios cu ON cu.id = @usuario_id
WHERE p.tracking COLLATE utf8mb4_general_ci = @paquete_tracking
  AND p.almacen_id = cu.almacen_id
ORDER BY t.fecha DESC
LIMIT 5;
```


## Explicación de la Consulta:

JOIN: Une la tabla t_tracking con t_paquete para obtener el tracking, estado_actual y almacen_id.

LEFT JOIN: Une con t_usuarios para obtener el nombre del operario, permitiendo que el movimiento exista aunque el usuario haya sido eliminado.

WHERE: Aplica dos filtros de seguridad:

Filtra por el tracking del paquete deseado.

Filtra por el almacen_id (simulando que se usa el ID de almacén del usuario logueado), impidiendo que un usuario vea movimientos de paquetes que no están en su almacén.

ORDER BY: Ordena por la fecha de movimiento en orden descendente.

LIMIT 5: Restringe el resultado a los últimos 5 movimientos.


## 💻 Ejecución Local

Para correr el backend de forma local se recomienda usar **XAMPP** (Apache + MySQL) en tu máquina.

### Requisitos Previos

1. XAMPP instalado y funcionando.
2. PHP >= 8.0.
3. MySQL usando la URl, puede ser la base de datos de manera local o deployada en la nube.
4. Composer (opcional, solo si se usan dependencias adicionales).

---

### 1. Como se Configuro la Base de Datos

1. Se Abrio **MySQLWoorbech** se uso la consola
2. Se creo la base de datos `wms_db` ejecutando el script:

```sql

-- Ejecutando el contenido de sql/schema.sql
Esto crea la base de datos y Ejecuta las tablas y relaciones definidas en sql/schema.sql.

luego se cambio la variable de entorno, para que se conecte a la nueva base de datos se ha creado

MYSQL_URL=mysql://<USUARIO>:<CONTRASEÑA>@<HOST>:<PUERTO>/<NOMBRE_BD> Cambiando <NOMBRE_BD>. 

Inserte un usuario de prueba
[https://vms-backend-s4sj.onrender.com/utils/crear_usuario]

Se puede insertar mas usuarios para prueba cambiando el correo en el script ya que no se pueden suplicar los correos.

