# 📋 INSTRUCCIONES DE INSTALACIÓN — OTEC Platform

## 1. Crear la base de datos

Ve a: `http://localhost/phpmyadmin`

Crea la base de datos llamada: **otec_platform**

Luego ve a la pestaña **SQL** y pega todo lo siguiente:

---

```sql
USE otec_platform;

-- Tabla de usuarios (alumnos, docentes, admins)
CREATE TABLE users (
    id_user    INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100)  NOT NULL,
    email      VARCHAR(120)  UNIQUE NOT NULL,
    password   VARCHAR(255)  NOT NULL,
    role       VARCHAR(20)   NOT NULL DEFAULT 'alumno',
    created_at TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de cursos
CREATE TABLE cursos (
    id_curso    INT AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(60)  NOT NULL UNIQUE,
    title       VARCHAR(100) NOT NULL,
    description VARCHAR(500) NOT NULL,
    ruta        VARCHAR(255) NOT NULL,
    estado      VARCHAR(10)  NOT NULL DEFAULT 'activo'
);

-- Tabla de inscripciones
CREATE TABLE enrollments (
    id_enroll   INT AUTO_INCREMENT PRIMARY KEY,
    id_user     INT NOT NULL,
    id_curso    INT NOT NULL,
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user)  REFERENCES users(id_user)  ON DELETE CASCADE,
    FOREIGN KEY (id_curso) REFERENCES cursos(id_curso) ON DELETE CASCADE,
    UNIQUE KEY uq_enrollment (id_user, id_curso)
);

-- Tabla de reportes / contacto
CREATE TABLE reports (
    id_report INT AUTO_INCREMENT PRIMARY KEY,
    name      VARCHAR(100),
    email     VARCHAR(120),
    title     VARCHAR(150),
    msg       TEXT,
    file      VARCHAR(255) NOT NULL DEFAULT '',
    datet     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de videos (global, para la sección videoscache)
CREATE TABLE videos (
    id_video INT AUTO_INCREMENT PRIMARY KEY,
    nombre   VARCHAR(60)  NOT NULL,
    ruta     VARCHAR(255) NOT NULL
);

-- ====================================================
-- Crear un usuario ADMIN inicial para poder entrar
-- Usuario: admin@otec.cl  |  Contraseña: admin123
-- ====================================================
INSERT INTO users (name, email, password, role)
VALUES (
    'Administrador',
    'admin@otec.cl',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin'
);
-- NOTA: La contraseña hash de arriba corresponde a "password".
-- Cámbiala desde el panel de administración después de entrar.
```

---

## 2. Configurar la conexión

Edita el archivo `otec-platform/config/database.php` si tu MySQL usa usuario o contraseña diferente:

```php
$host     = "localhost";
$dbname   = "otec_platform";
$username = "root";
$password = "";  // Pon tu contraseña aquí si tienes
```

---

## 3. Copiar la carpeta al servidor

Copia la carpeta `otec-platform/` dentro de la carpeta de tu servidor local:

- **XAMPP**: `C:\xampp\htdocs\otec-platform\`
- **WAMP**: `C:\wamp\www\otec-platform\`
- **MAMP**: `/Applications/MAMP/htdocs/otec-platform/`

---

## 4. Acceder a la plataforma

Abre tu navegador y ve a:

```
http://localhost/otec-platform/
```

Para entrar como admin usa las credenciales del paso 1.

---

## Bugs corregidos en esta versión

| Archivo                     | Problema original                              | Corrección                                 |
|----------------------------|------------------------------------------------|--------------------------------------------|
| `user/edit-profile.php`    | Usaba `id` en vez de `id_user`                 | Cambiado a `id_user` en todas las queries  |
| `admin/manage-users.php`   | Usaba `id` en vez de `id_user` al eliminar     | Cambiado a `id_user`                       |
| `courses/courses-hub.php`  | `echo` con concatenación rota (sin echo)       | Corregido el echo de los links             |
| `auth/register.php`        | Creaba tablas dinámicas por usuario (inseguro) | Eliminado, solo INSERT a users             |
| `courses/create-course.php`| Nombre del curso no sanitizado para SQL        | Regex de sanitización agregado             |
| `enrollments/enroll.php`   | Sin validación de sesión al inicio             | Se usa auth-check.php                      |
| `my-courses.php`           | HTML fuera del PHP, estructura rota            | Reordenado correctamente                   |
