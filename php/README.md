# Huellitas de Amor (PHP + MySQL)

Versión PHP del sistema del refugio. Misma idea del informe (5 roles, animales, adopciones, historial médico, donaciones, voluntariado, reportes), pero hecha con PHP plano + MySQL para correr fácil en XAMPP / WAMP / MAMP.

## Requisitos

- PHP 7.4 o superior (probado en PHP 8.1).
- MySQL / MariaDB (con XAMPP es lo que viene por defecto).
- Apache (o `php -S` para desarrollo).

## Instalación rápida en XAMPP

1. Copia la carpeta `php/` dentro de `htdocs`. Por ejemplo: `C:\xampp\htdocs\php\`.
2. Inicia Apache y MySQL desde el panel de XAMPP.
3. (Opcional) Edita `php/includes/config.php` si usas otro usuario / contraseña de MySQL. Por defecto usa `root` sin contraseña, que es lo de XAMPP.
4. Abre **una sola vez** en tu navegador: <http://localhost/php/install.php>
   - Crea la base **`DB_REFUJIO`**, todas las tablas y datos demo (5 usuarios, 5 animales, 1 adopción pendiente, 1 atención médica, 2 donaciones, 1 actividad de voluntariado).
5. Listo. Entra a <http://localhost/php/login.php>.

## Usuarios demo

| Rol           | Email                    | Contraseña |
|---------------|--------------------------|------------|
| Administrador | admin@refugio.bo         | admin123   |
| Veterinario   | vet@refugio.bo           | vet123     |
| Adoptante     | adoptante@refugio.bo     | adopta123  |
| Donante       | donante@refugio.bo       | dona123    |
| Voluntario    | voluntario@refugio.bo    | volun123   |

## Estructura

```
php/
├── includes/
│   ├── config.php       # credenciales BD + nombre del sitio
│   ├── db.php           # conexion PDO
│   ├── auth.php         # session, csrf, roles, helpers
│   ├── header.php       # nav filtrada por rol
│   └── footer.php
├── assets/style.css
├── db.sql               # esquema MySQL puro
├── install.php          # 1 click: crea BD + tablas + seed
├── index.php            # landing publica
├── login.php / logout.php / registro.php
├── dashboard.php        # accesos por rol
├── animales.php / animal_form.php / animal_detalle.php
├── adopciones.php       # adoptante: pedir / admin: aprobar
├── historial_medico.php # vet/admin
├── donaciones.php       # donante/admin con comprobante
├── voluntariado.php     # actividades + inscripciones + horas
├── usuarios.php         # admin CRUD
└── reportes.php         # admin
```

## Mapeo a los diagramas del informe

| Diagrama / actor    | Pagina(s) PHP                                 |
|---------------------|-----------------------------------------------|
| Administrador       | usuarios.php, animal_form.php, adopciones.php (aprobar/rechazar), reportes.php |
| Veterinario         | historial_medico.php                          |
| Adoptante           | animal_detalle.php (solicitar), adopciones.php (mis solicitudes) |
| Donante             | donaciones.php (registrar + comprobante)      |
| Voluntario          | voluntariado.php (inscribirse + registrar horas) |
| Caso "Iniciar sesion" | login.php / logout.php                       |
| Caso "Generar reportes" | reportes.php                              |

## Logica de adopcion

Para mantenerlo simple:

- El adoptante envia una solicitud sobre un animal **disponible**. Si ya tiene una solicitud pendiente o aprobada para el mismo animal, no puede duplicar.
- El admin **Aprueba** o **Rechaza** desde `adopciones.php`. Al aprobar:
  - El animal pasa directo a estado `adoptado`.
  - Las demas solicitudes pendientes para ese animal se marcan automaticamente como `rechazada` con una observacion.
- Al rechazar, el animal sigue disponible.

## Logica de roles

`includes/auth.php` ofrece `require_login()` y `require_role([...])`. Cada pagina sensible los usa al inicio. La nav (`includes/header.php`) tambien filtra los enlaces segun rol para no mostrar opciones que el usuario no puede usar.

## Notas

- Todos los formularios POST usan token CSRF (`csrf_token()` + `csrf_verify()`).
- Las contrase&ntilde;as se guardan con `password_hash(PASSWORD_BCRYPT)`.
- `install.php` borra y recarga datos demo. Si ya hay datos reales en `DB_REFUJIO`, **NO** lo ejecutes de nuevo: usa solo `db.sql` para crear las tablas.
