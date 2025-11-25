# 📦 Sistema de Gestión de Almacén (WMS) - Backend PHP

Este proyecto implementa la capa de backend para un Sistema de Gestión de Almacén (Warehouse Management System o WMS), enfocándose en la seguridad, la autenticación basada en roles y la restricción de datos por almacén asignado al usuario.

El backend está desarrollado en **PHP** y utiliza la extensión **PDO** para una gestión segura de la base de datos MySQL.

---

## 🚀 Despliegue y URL Base

El servicio está desplegado y accesible en la siguiente URL base:  
[https://vms-backend-s4sj.onrender.com](https://vms-backend-s4sj.onrender.com)

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
| config/database.php | (Clase) | N/A | Clase `Database` que gestiona la conexión a la BD usando variables de entorno (`DB_HOST`, `DB_USER`, etc.). |
| utils/helpers.php | (Funciones) | N/A | Provee `json_input`, `send_json`, y `get_bearer_token`. |

### A. Endpoint: Inicio de Sesión (POST /auth/login)

**Objetivo:** Obtener un token de acceso para las peticiones subsiguientes.

**Petición de Ejemplo (cURL):**

```bash
curl -X POST 'https://vms-backend-s4sj.onrender.com/auth/login' \
-H 'Content-Type: application/json' \
-d '{"email": "operador@otp.com", "password": "123456"}'
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
B. Endpoint: Verificar Sesión (GET /auth/me)

Objetivo: Validar un token de sesión y recuperar los datos del usuario.

Petición de Ejemplo (cURL):
```bash
curl -X GET 'https://vms-backend-s4sj.onrender.com/auth/me' \
-H 'Authorization: Bearer <TOKEN_GENERADO_PREVIAMENTE>'
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


📊 Parte 2: Esquema SQL para WMS

Tablas Requeridas

Las siguientes tablas se definen en sql/schema.sql y son la base de datos del WMS, incluyendo la relación clave entre usuarios y almacenes.

🔍 Tarea SQL (Tracking Real de Paquetes)

A continuación, se presenta la consulta SQL que simula el seguimiento logístico, devolviendo los últimos 5 movimientos de un paquete específico, filtrando por el almacén del usuario para asegurar la seguridad y el alcance correcto de los datos.

Consulta SQL (Últimos 5 Movimientos con Seguridad de Almacén)
Insertar datos de prueba:

-- Insertar paquetes
```bash
INSERT INTO t_paquete (tracking, descripcion, peso, estado_actual, almacen_id) VALUES
('PKG001', 'Paquete de prueba 1', 2.5, 'INGRESO', 2),
('PKG002', 'Paquete de prueba 2', 1.2, 'PICKING', 2);
```

-- Insertar movimientos
```bash
INSERT INTO t_tracking (paquete_id, usuario_id, tipo_movimiento, descripcion) VALUES
(1, 5, 'INGRESO', 'Paquete recibido en almacén'),
(1, 5, 'PICKING', 'Paquete preparado para salida'),
(2, 5, 'INGRESO', 'Paquete recibido en almacén');
```


Probar la consulta de nuevo, reemplazando @usuario_id y @paquete_tracking por valores reales:
```bash
SET @usuario_id = 5;
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


Explicación de la Consulta:

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
3. MySQL corriendo en el puerto **3307** (si estás usando el puerto por defecto de XAMPP, verifica en `xampp/control.ini` o MySQL config).
4. Composer (opcional, solo si se usan dependencias adicionales).

---

### 1. Configuración de la Base de Datos

1. Abre **phpMyAdmin** o usa la consola de MySQL.
2. Crea la base de datos `wms_db` ejecutando el script:

```sql
CREATE DATABASE IF NOT EXISTS wms_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE wms_db;

-- Ejecutar luego el contenido de sql/schema.sql
Ejecuta las tablas y relaciones definidas en sql/schema.sql.

Inserta un usuario de prueba usando utils/crear_usuario.php