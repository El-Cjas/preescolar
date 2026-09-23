<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planilla de Inscripción - {{ $alumno->persona->nombre }} {{ $alumno->persona->apellido }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            @page {
                size: letter portrait;
                margin: 10mm 12mm 10mm 12mm;
            }
            body {
                background: white !important;
                color: black !important;
                font-size: 11pt;
            }
            .no-print {
                display: none !important;
            }
            .print-border {
                border-color: #333 !important;
            }
        }
    </style>
</head>
<body class="bg-zinc-100 text-zinc-900 min-h-screen py-6 print:py-0 print:bg-white">

    <!-- BARRA SUPERIOR DE ACCIONES (No imprimible) -->
    <div class="max-w-4xl mx-auto mb-6 px-4 no-print flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-2">
            <button onclick="window.history.length > 1 ? window.history.back() : window.location.href='{{ route('alumnos.index') }}'" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-zinc-300 bg-white hover:bg-zinc-50 text-sm font-medium text-zinc-700 shadow-sm transition cursor-pointer">
                ← Volver
            </button>
            <span class="text-xs text-zinc-500 hidden md:inline">
                Sugerencia: En la ventana de impresión, puedes elegir "Guardar como PDF".
            </span>
        </div>

        <button onclick="window.print()" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow-md transition cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            Imprimir / Guardar en PDF
        </button>
    </div>

    <!-- DOCUMENTO OFICIAL (Hoja de Planilla) -->
    <div class="max-w-4xl mx-auto bg-white border border-zinc-300 shadow-lg rounded-xl p-8 print:border-0 print:shadow-none print:p-0 print:rounded-none">
        
        <!-- MEMBRETE OFICIAL INSTITUCIONAL -->
        <div class="flex items-start justify-between border-b-2 border-zinc-800 pb-4 mb-4">
            <div class="flex-1 text-center sm:text-left">
                <div class="text-[10px] sm:text-xs font-bold uppercase tracking-wide leading-tight text-zinc-800">
                    República Bolivariana de Venezuela<br>
                    Ministerio del Poder Popular para la Educación<br>
                    Centro de Educación Inicial "Preescolar Simón Bolívar"<br>
                    Circuito Escolar N° 04 · Código DEA: OD-04122026
                </div>
                <h1 class="mt-3 text-lg sm:text-xl font-extrabold uppercase tracking-wide text-zinc-900 border-t border-zinc-400 pt-2">
                    Planilla Oficial de Inscripción Escolar
                </h1>
                <div class="text-xs font-semibold text-zinc-600">
                    Año Escolar: 2025 - 2026 | Nivel: Educación Inicial (Preescolar)
                </div>
            </div>

            <!-- Recuadro para Foto Carnet -->
            <div class="w-24 h-28 sm:w-28 sm:h-32 border-2 border-dashed border-zinc-400 rounded-md flex flex-col items-center justify-center p-2 text-center bg-zinc-50 ml-4 print-border">
                <span class="text-[9px] uppercase font-bold text-zinc-500 leading-tight">Foto Tipo Carnet</span>
                <span class="text-[8px] text-zinc-400 mt-1">(Fondo blanco)</span>
            </div>
        </div>

        <!-- CÉDULA ESCOLAR DESTACADA -->
        <div class="bg-zinc-100 border border-zinc-300 rounded-lg p-2.5 mb-4 flex flex-col sm:flex-row justify-between items-center print:bg-zinc-50">
            <div class="text-xs uppercase font-bold text-zinc-700">
                Cédula Escolar Oficial (11 Dígitos):
            </div>
            <div class="text-base sm:text-lg font-mono font-bold tracking-widest text-blue-900 print:text-black">
                {{ $alumno->persona->prefijo }}-{{ $alumno->persona->cedula }}
            </div>
            <div class="text-[11px] text-zinc-500">
                Fecha de Inscripción: {{ $alumno->fecha_ingreso->format('d/m/Y') }}
            </div>
        </div>

        <!-- 1. DATOS DE IDENTIFICACIÓN DEL ALUMNO -->
        <div class="mb-4">
            <div class="bg-zinc-800 text-white text-xs font-bold uppercase px-3 py-1 rounded-t print:bg-zinc-900">
                1. Datos del Alumno(a)
            </div>
            <div class="border border-t-0 border-zinc-300 p-3 space-y-2 text-xs">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <div>
                        <span class="text-zinc-500 font-medium">Nombres:</span>
                        <span class="font-bold text-zinc-900 ml-1">{{ $alumno->persona->nombre }}</span>
                    </div>
                    <div>
                        <span class="text-zinc-500 font-medium">Apellidos:</span>
                        <span class="font-bold text-zinc-900 ml-1">{{ $alumno->persona->apellido }}</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 pt-1 border-t border-zinc-200">
                    <div>
                        <span class="text-zinc-500 font-medium">Fecha Nacimiento:</span>
                        <span class="font-semibold block">{{ $alumno->persona->fecha_nacimiento->format('d/m/Y') }}</span>
                    </div>
                    <div>
                        <span class="text-zinc-500 font-medium">Edad Cronológica:</span>
                        <span class="font-bold text-blue-700 print:text-black block">{{ $alumno->persona->fecha_nacimiento->age }} años</span>
                    </div>
                    <div>
                        <span class="text-zinc-500 font-medium">Género:</span>
                        <span class="font-semibold block">{{ $alumno->persona->genero === 'M' ? 'Masculino (Niño)' : 'Femenino (Niña)' }}</span>
                    </div>
                    <div>
                        <span class="text-zinc-500 font-medium">Lugar de Nac.:</span>
                        <span class="font-semibold block">{{ $alumno->lugar_nacimiento }}</span>
                    </div>
                </div>

                <div class="pt-1 border-t border-zinc-200">
                    <span class="text-zinc-500 font-medium">Dirección de Habitación:</span>
                    <span class="font-medium text-zinc-800 ml-1">{{ $alumno->persona->direccion }}</span>
                </div>
            </div>
        </div>

        <!-- 2. FICHA MÉDICA Y ANTROPOMÉTRICA -->
        <div class="mb-4">
            <div class="bg-zinc-800 text-white text-xs font-bold uppercase px-3 py-1 rounded-t print:bg-zinc-900">
                2. Ficha Antropométrica y de Salud
            </div>
            <div class="border border-t-0 border-zinc-300 p-3 text-xs">
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 text-center">
                    <div class="p-2 border border-zinc-200 rounded bg-zinc-50 print:bg-white">
                        <div class="text-[10px] text-zinc-500 uppercase font-semibold">Tipo de Sangre</div>
                        <div class="text-base font-extrabold text-red-600 print:text-black mt-0.5">{{ $alumno->tipo_sangre }}</div>
                    </div>
                    <div class="p-2 border border-zinc-200 rounded bg-zinc-50 print:bg-white">
                        <div class="text-[10px] text-zinc-500 uppercase font-semibold">Peso</div>
                        <div class="text-sm font-bold text-zinc-800 mt-0.5">{{ $alumno->peso ?? 'N/A' }} kg</div>
                    </div>
                    <div class="p-2 border border-zinc-200 rounded bg-zinc-50 print:bg-white">
                        <div class="text-[10px] text-zinc-500 uppercase font-semibold">Estatura</div>
                        <div class="text-sm font-bold text-zinc-800 mt-0.5">{{ $alumno->estatura ?? 'N/A' }} m</div>
                    </div>
                    <div class="p-2 border border-zinc-200 rounded bg-zinc-50 print:bg-white">
                        <div class="text-[10px] text-zinc-500 uppercase font-semibold">Talla Camisa</div>
                        <div class="text-sm font-bold text-zinc-800 mt-0.5">Talla {{ $alumno->talla_camisa ?? 'N/A' }}</div>
                    </div>
                    <div class="p-2 border border-zinc-200 rounded bg-zinc-50 print:bg-white col-span-2 sm:col-span-1">
                        <div class="text-[10px] text-zinc-500 uppercase font-semibold">Talla Pantalón</div>
                        <div class="text-sm font-bold text-zinc-800 mt-0.5">Talla {{ $alumno->talla_pantalon ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. DATOS DE LOS REPRESENTANTES / FAMILIA -->
        <div class="mb-5">
            <div class="bg-zinc-800 text-white text-xs font-bold uppercase px-3 py-1 rounded-t print:bg-zinc-900">
                3. Datos del Representante Legal y Contacto de Emergencia
            </div>
            <div class="border border-t-0 border-zinc-300 p-3 space-y-2 text-xs">
                @if ($representanteLegal)
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                        <div>
                            <span class="text-zinc-500 font-medium">Nombre Completo:</span>
                            <span class="font-bold text-zinc-900 block">{{ $representanteLegal->persona->nombre }} {{ $representanteLegal->persona->apellido }}</span>
                        </div>
                        <div>
                            <span class="text-zinc-500 font-medium">Cédula de Identidad:</span>
                            <span class="font-mono font-bold block">{{ $representanteLegal->persona->prefijo }}-{{ $representanteLegal->persona->cedula }}</span>
                        </div>
                        <div>
                            <span class="text-zinc-500 font-medium">Parentesco:</span>
                            <span class="font-semibold text-blue-800 print:text-black block">{{ $representanteLegal->pivot->parentesco }} (Representante Legal)</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 pt-1 border-t border-zinc-200">
                        <div>
                            <span class="text-zinc-500 font-medium">Ocupación / Oficio:</span>
                            <span class="font-semibold block">{{ $representanteLegal->ocupacion }}</span>
                        </div>
                        <div>
                            <span class="text-zinc-500 font-medium">Teléfono de Contacto:</span>
                            <span class="font-semibold block">{{ $representanteLegal->persona->codigo_tlf }}-{{ $representanteLegal->persona->numero_tlf }}</span>
                        </div>
                        <div>
                            <span class="text-zinc-500 font-medium">Correo Electrónico:</span>
                            <span class="font-semibold block truncate">{{ $representanteLegal->persona->email }}</span>
                        </div>
                    </div>

                    <div class="pt-1 border-t border-zinc-200">
                        <span class="text-zinc-500 font-medium">Dirección de Habitación:</span>
                        <span class="font-medium text-zinc-800 ml-1">{{ $representanteLegal->persona->direccion }}</span>
                    </div>
                @else
                    <div class="text-center py-2 text-zinc-400 italic">
                        No registra representante legal formalmente asignado.
                    </div>
                @endif

                <!-- Otros Representantes Asociados si existen -->
                @php
                    $otrosRepresentantes = $alumno->representantes->where('id', '!=', $representanteLegal?->id);
                @endphp
                @if ($otrosRepresentantes->count() > 0)
                    <div class="pt-2 border-t border-zinc-300">
                        <span class="text-zinc-500 font-semibold block mb-1">Otros Familiares Registrados:</span>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-[11px]">
                            @foreach ($otrosRepresentantes as $otro)
                                <div class="p-1.5 bg-zinc-50 border border-zinc-200 rounded">
                                    <strong>{{ $otro->persona->nombre }} {{ $otro->persona->apellido }}</strong> ({{ $otro->pivot->parentesco }})<br>
                                    C.I: {{ $otro->persona->prefijo }}-{{ $otro->persona->cedula }} | Tlf: {{ $otro->persona->codigo_tlf }}-{{ $otro->persona->numero_tlf }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- 4. ACTA DE COMPROMISO, FIRMAS Y SELLO -->
        <div class="border border-zinc-300 rounded p-3 text-xs bg-zinc-50/50 print:bg-white">
            <div class="text-justify text-[10.5px] leading-relaxed text-zinc-700">
                <strong>ACTA DE COMPROMISO INSTITUCIONAL:</strong> Yo, en mi condición de Representante Legal del alumno(a) plenamente identificado en este documento, certifico que los datos suministrados son exactos y fidedignos. Asimismo, me comprometo formalmente a colaborar activamente en el proceso formativo de mi representado, velar por su asistencia puntual, cumplir con los acuerdos de convivencia escolar y respetar las directrices pedagógicas del Centro de Educación Inicial.
            </div>

            <!-- Recuadros de Firmas y Sello -->
            <div class="grid grid-cols-3 gap-4 pt-12 mt-4 text-center">
                <!-- Firma Representante -->
                <div class="flex flex-col items-center">
                    <div class="w-full border-t border-zinc-800 pt-1">
                        <div class="font-bold text-[11px] uppercase">Firma del Representante Legal</div>
                        <div class="text-[10px] text-zinc-500">C.I.: {{ $representanteLegal?->persona->prefijo }}-{{ $representanteLegal?->persona->cedula }}</div>
                        <div class="w-12 h-14 border border-dashed border-zinc-400 mt-2 mx-auto flex items-center justify-center text-[8px] text-zinc-400">
                            Huella
                        </div>
                    </div>
                </div>

                <!-- Firma Dirección -->
                <div class="flex flex-col items-center justify-between">
                    <div class="w-full border-t border-zinc-800 pt-1">
                        <div class="font-bold text-[11px] uppercase">Dirección del Plantel</div>
                        <div class="text-[10px] text-zinc-500">Firma Autorizada</div>
                    </div>
                    <div class="text-[9px] text-zinc-400 italic">
                        Control de Estudios y Evaluación
                    </div>
                </div>

                <!-- Sello Húmedo -->
                <div class="flex flex-col items-center justify-center">
                    <div class="w-24 h-24 border-2 border-dashed border-zinc-400 rounded-full flex items-center justify-center text-center p-2">
                        <span class="text-[9px] font-bold uppercase text-zinc-400 leading-tight">
                            Sello Húmedo Institucional
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- PIE DE PÁGINA DOCUMENTAL -->
        <div class="mt-4 pt-2 border-t border-zinc-200 text-center text-[9px] text-zinc-400 flex justify-between">
            <span>Expediente Escolar · Centro de Educación Inicial</span>
            <span>Fecha de Emisión: {{ now()->format('d/m/Y h:i A') }}</span>
            <span>Sistema Escolar Preescolar</span>
        </div>
    </div>

</body>
</html>
