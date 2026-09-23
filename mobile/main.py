import flet as ft
import httpx

# ==========================================
# CONFIGURACIÓN DE CONEXIÓN
# ==========================================
# Si ejecutas la app en tu PC de escritorio:
API_BASE_URL = "http://192.168.0.116:8000/api"

# Si usas Emulador de Android Studio, cambia a:
# API_BASE_URL = "http://10.0.2.2:8000/api"

# Si usas tu teléfono físico por WiFi, pon la IP local de tu PC:
# API_BASE_URL = "http://192.168.1.XX:8000/api"

# Variable para almacenar el token de sesión
auth_token = None


def main(page: ft.Page):
    page.title = "Preescolar Móvil"
    page.theme_mode = ft.ThemeMode.LIGHT
    page.window.width = 420
    page.window.height = 750
    page.padding = 0

    # ------------------------------------------
    # MODAL: FICHA MÉDICA Y EXPEDIENTE DEL ALUMNO
    # ------------------------------------------
    def ver_ficha_alumno(alumno):
        rep = alumno.get("representante")
        rep_info = (
            f"Representante: {rep['nombre_completo']} ({rep['parentesco']})\n"
            f"Cédula: {rep['cedula']}\n"
            f"Teléfono: {rep['telefono']}\n"
            f"Correo: {rep['email']}"
            if rep
            else "Sin representante asignado"
        )

        def cerrar_dialogo(e):
            page.pop_dialog()

        url_ficha = f"{API_BASE_URL}/alumnos/{alumno['id']}/ficha?token={auth_token}"

        dialog = ft.AlertDialog(
            title=ft.Text(f"Ficha: {alumno['nombre_completo']}", weight=ft.FontWeight.BOLD),
            content=ft.Column(
                [
                    ft.Text(f"Cédula Escolar: {alumno['cedula_escolar']}", weight=ft.FontWeight.W_500),
                    ft.Text(f"Edad: {alumno['edad']} años | Género: {alumno['genero']}"),
                    ft.Divider(),
                    ft.Text("Datos Antropométricos y Médicos:", weight=ft.FontWeight.BOLD, size=13),
                    ft.Text(f"• Tipo de Sangre: {alumno['tipo_sangre']}"),
                    ft.Text(f"• Peso: {alumno.get('peso') or 'N/A'} kg"),
                    ft.Text(f"• Estatura: {alumno.get('estatura') or 'N/A'} m"),
                    ft.Text(f"• Talla Camisa: {alumno.get('talla_camisa') or 'N/A'}"),
                    ft.Text(f"• Talla Pantalón: {alumno.get('talla_pantalon') or 'N/A'}"),
                    ft.Divider(),
                    ft.Text("Contacto de Emergencia:", weight=ft.FontWeight.BOLD, size=13),
                    ft.Text(rep_info, size=12),
                ],
                tight=True,
                scroll=ft.ScrollMode.AUTO,
            ),
            actions=[
                ft.FilledButton(
                    content="Planilla Oficial (PDF)",
                    icon=ft.Icons.PRINT,
                    url=url_ficha,
                ),
                ft.TextButton(content="Cerrar", on_click=cerrar_dialogo),
            ],
        )

        page.show_dialog(dialog)

    # ------------------------------------------
    # VISTA 2: LISTA DE ALUMNOS DEL PLANTEL
    # ------------------------------------------
    def mostrar_pantalla_alumnos():
        page.clean()

        lista_alumnos = ft.ListView(expand=True, spacing=10, padding=15)
        indicador_carga = ft.ProgressBar(visible=True)
        mensaje_estado = ft.Text(value="", color=ft.Colors.GREY_600, text_align=ft.TextAlign.CENTER)

        def cargar_alumnos(busqueda=""):
            indicador_carga.visible = True
            lista_alumnos.controls.clear()
            page.update()

            try:
                headers = {"Authorization": f"Bearer {auth_token}"}
                params = {"search": busqueda} if busqueda else {}

                resp = httpx.get(f"{API_BASE_URL}/alumnos", headers=headers, params=params, timeout=10.0)

                if resp.status_code == 200:
                    datos = resp.json().get("data", [])

                    if not datos:
                        mensaje_estado.value = "No se encontraron alumnos."
                        lista_alumnos.controls.append(mensaje_estado)
                    else:
                        for alumno in datos:
                            # Color del avatar según el género
                            es_nino = alumno.get("genero") == "M"
                            avatar_color = ft.Colors.BLUE_400 if es_nino else ft.Colors.PINK_300
                            iniciales = alumno["nombre"][:1] + alumno["apellido"][:1]

                            card = ft.Card(
                                content=ft.Container(
                                    content=ft.ListTile(
                                        leading=ft.CircleAvatar(
                                            content=ft.Text(iniciales, color=ft.Colors.WHITE, weight=ft.FontWeight.BOLD),
                                            bgcolor=avatar_color,
                                        ),
                                        title=ft.Text(alumno["nombre_completo"], weight=ft.FontWeight.W_600),
                                        subtitle=ft.Text(
                                            f"C.E.: {alumno['cedula_escolar']} · {alumno['edad']} años\n"
                                            f"Sangre: {alumno['tipo_sangre']}"
                                        ),
                                        trailing=ft.Icon(ft.Icons.ARROW_FORWARD_IOS, size=14, color=ft.Colors.GREY_400),
                                        on_click=lambda e, al=alumno: ver_ficha_alumno(al),
                                    ),
                                    padding=8,
                                ),
                                elevation=2,
                            )
                            lista_alumnos.controls.append(card)
                else:
                    page.show_dialog(ft.SnackBar(content=ft.Text("Error al cargar alumnos desde el servidor.")))

            except Exception as ex:
                lista_alumnos.controls.append(ft.Text(f"Error de conexión: {ex}", color=ft.Colors.RED_500))

            indicador_carga.visible = False
            page.update()

        def on_buscar_change(e):
            cargar_alumnos(e.control.value)

        def cerrar_sesion(e):
            global auth_token
            try:
                headers = {"Authorization": f"Bearer {auth_token}"}
                httpx.post(f"{API_BASE_URL}/logout", headers=headers, timeout=5.0)
            except Exception:
                pass
            auth_token = None
            mostrar_pantalla_login()

        url_censo = f"{API_BASE_URL}/alumnos/exportar/csv?token={auth_token}"

        # Barra superior (AppBar)
        app_bar = ft.AppBar(
            leading=ft.Icon(ft.Icons.SCHOOL, color=ft.Colors.WHITE),
            title=ft.Text("Alumnos - Preescolar", color=ft.Colors.WHITE, weight=ft.FontWeight.BOLD, size=18),
            bgcolor=ft.Colors.BLUE_700,
            actions=[
                ft.IconButton(
                    icon=ft.Icons.FILE_DOWNLOAD,
                    icon_color=ft.Colors.WHITE,
                    tooltip="Descargar Censo Excel",
                    url=url_censo,
                ),
                ft.IconButton(
                    icon=ft.Icons.LOGOUT,
                    icon_color=ft.Colors.WHITE,
                    tooltip="Cerrar Sesión",
                    on_click=cerrar_sesion,
                ),
            ],
        )

        campo_busqueda = ft.TextField(
            hint_text="Buscar por nombre o cédula...",
            prefix_icon=ft.Icons.SEARCH,
            on_change=on_buscar_change,
            border_radius=12,
            filled=True,
            dense=True,
        )

        barra_censo = ft.Container(
            content=ft.Row(
                [
                    ft.Row(
                        [
                            ft.Icon(ft.Icons.DESCRIPTION_OUTLINED, size=18, color=ft.Colors.BLUE_700),
                            ft.Text("Censo Escolar", weight=ft.FontWeight.BOLD, size=13),
                        ],
                        spacing=6,
                    ),
                    ft.FilledButton(
                        content="Descargar Excel",
                        icon=ft.Icons.FILE_DOWNLOAD,
                        url=url_censo,
                        height=36,
                    ),
                ],
                alignment=ft.MainAxisAlignment.SPACE_BETWEEN,
            ),
            padding=ft.Padding.only(left=15, right=15, top=5, bottom=5),
        )

        page.add(
            app_bar,
            ft.Container(content=campo_busqueda, padding=ft.Padding.only(left=15, right=15, top=12, bottom=2)),
            barra_censo,
            indicador_carga,
            lista_alumnos,
        )

        # Cargar lista inicial
        cargar_alumnos()

    # ------------------------------------------
    # VISTA 1: INICIO DE SESIÓN (LOGIN)
    # ------------------------------------------
    def mostrar_pantalla_login():
        page.clean()

        txt_email = ft.TextField(
            label="Correo Electrónico",
            value="admin@preescolar.test",
            prefix_icon=ft.Icons.EMAIL_OUTLINED,
            border_radius=10,
        )
        txt_password = ft.TextField(
            label="Contraseña",
            value="password",
            password=True,
            can_reveal_password=True,
            prefix_icon=ft.Icons.LOCK_OUTLINED,
            border_radius=10,
        )
        lbl_error = ft.Text(value="", color=ft.Colors.RED_600, size=12)
        progreso = ft.ProgressBar(visible=False)

        def ejecutar_login(e):
            global auth_token
            lbl_error.value = ""
            progreso.visible = True
            btn_login.disabled = True
            page.update()

            try:
                resp = httpx.post(
                    f"{API_BASE_URL}/login",
                    json={"email": txt_email.value.strip(), "password": txt_password.value.strip()},
                    timeout=10.0,
                )

                if resp.status_code == 200:
                    auth_token = resp.json().get("token")
                    mostrar_pantalla_alumnos()
                    return
                elif resp.status_code == 401:
                    lbl_error.value = "Correo o contraseña incorrectos."
                else:
                    lbl_error.value = f"Error del servidor ({resp.status_code})"

            except Exception as ex:
                lbl_error.value = f"No se pudo conectar a Laravel: {ex}"

            progreso.visible = False
            btn_login.disabled = False
            page.update()

        btn_login = ft.FilledButton(
            content="Iniciar Sesión",
            icon=ft.Icons.LOGIN,
            width=280,
            height=48,
            on_click=ejecutar_login,
        )

        def abrir_config_servidor(e):
            txt_servidor = ft.TextField(
                label="URL del Servidor",
                value=API_BASE_URL,
                border_radius=8,
                dense=True,
                hint_text="Ej: http://192.168.0.116:8000/api",
            )

            def guardar_servidor(ev):
                global API_BASE_URL
                val = txt_servidor.value.strip().rstrip("/")
                if val:
                    API_BASE_URL = val
                page.pop_dialog()
                page.show_dialog(ft.SnackBar(content=ft.Text(f"Servidor actualizado: {API_BASE_URL}")))

            page.show_dialog(
                ft.AlertDialog(
                    title=ft.Text("Configuración de Servidor", weight=ft.FontWeight.BOLD),
                    content=ft.Column(
                        [
                            ft.Text("Si la IP de tu PC cambia o usas HTTPS, edítala aquí:", size=13),
                            txt_servidor,
                        ],
                        tight=True,
                    ),
                    actions=[
                        ft.FilledButton(content="Guardar", on_click=guardar_servidor),
                        ft.TextButton(content="Cancelar", on_click=lambda ev: page.pop_dialog()),
                    ],
                )
            )

        login_card = ft.Card(
            content=ft.Container(
                content=ft.Column(
                    [
                        ft.Row(
                            [
                                ft.IconButton(
                                    icon=ft.Icons.SETTINGS,
                                    icon_color=ft.Colors.GREY_500,
                                    tooltip="Configurar IP del Servidor",
                                    on_click=abrir_config_servidor,
                                ),
                            ],
                            alignment=ft.MainAxisAlignment.END,
                        ),
                        ft.Icon(ft.Icons.CHILD_CARE, size=55, color=ft.Colors.BLUE_600),
                        ft.Text("Preescolar Bolívar", size=22, weight=ft.FontWeight.BOLD),
                        ft.Text("Acceso al Personal y Directiva", size=13, color=ft.Colors.GREY_600),
                        ft.Container(height=10),
                        txt_email,
                        txt_password,
                        lbl_error,
                        progreso,
                        ft.Container(height=10),
                        btn_login,
                    ],
                    horizontal_alignment=ft.CrossAxisAlignment.CENTER,
                ),
                padding=20,
            ),
            elevation=4,
        )

        page.add(
            ft.Container(
                content=login_card,
                alignment=ft.Alignment.CENTER,
                expand=True,
                padding=20,
            )
        )

    # Iniciar en la pantalla de login
    mostrar_pantalla_login()


if __name__ == "__main__":
    ft.run(
        main,
        host="0.0.0.0",
        port=8550,
        view=ft.AppView.WEB_BROWSER,
    )