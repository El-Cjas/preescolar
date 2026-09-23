<div class="flex flex-col gap-6">
    <!-- Encabezado de Bienvenida y Acciones Rápidas -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-gradient-to-r from-blue-500/10 via-indigo-500/5 to-transparent p-6 rounded-2xl border border-blue-100 dark:border-blue-900/30">
        <div>
            <div class="flex items-center gap-2 text-blue-600 dark:text-blue-400 font-semibold text-xs tracking-wider uppercase">
                <flux:icon icon="academic-cap" class="w-4 h-4" />
                Centro de Educación Inicial
            </div>
            <flux:heading size="xl" level="1" class="mt-1">Panel de Control Directivo</flux:heading>
            <flux:subheading>Resumen en tiempo real de la matrícula escolar, representantes y plantilla docente.</flux:subheading>
        </div>

        <!-- Botones de Acción Rápida -->
        <div class="flex flex-wrap items-center gap-2">
            <flux:button variant="primary" icon="academic-cap" href="{{ route('alumnos.index') }}" wire:navigate>
                Inscribir Alumno
            </flux:button>
            <flux:button variant="outline" icon="user-plus" href="{{ route('representantes.index') }}" wire:navigate>
                Representantes
            </flux:button>
            <flux:button variant="outline" icon="briefcase" href="{{ route('trabajadores.index') }}" wire:navigate>
                Personal
            </flux:button>
            <flux:button variant="outline" icon="arrow-down-tray" href="{{ route('alumnos.export.csv') }}">
                Exportar Censo
            </flux:button>
        </div>
    </div>

    <!-- Tarjetas de Estadísticas Principales (KPIs) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Matrícula Escolar -->
        <flux:card class="flex flex-col justify-between space-y-3">
            <div class="flex justify-between items-start">
                <span class="text-sm font-medium text-zinc-500">Matrícula Escolar</span>
                <div class="p-2 bg-blue-50 dark:bg-blue-950/50 rounded-lg text-blue-600 dark:text-blue-400">
                    <flux:icon icon="academic-cap" class="w-5 h-5" />
                </div>
            </div>
            <div>
                <div class="text-3xl font-bold text-zinc-900 dark:text-zinc-100">{{ $totalAlumnos }}</div>
                <div class="text-xs text-zinc-500 mt-1 flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 text-blue-600 font-medium">
                        👦 {{ $totalNinos }} niños
                    </span>
                    <span>·</span>
                    <span class="inline-flex items-center gap-1 text-pink-600 font-medium">
                        👧 {{ $totalNinas }} niñas
                    </span>
                </div>
            </div>
            <div class="pt-2 border-t border-zinc-100 dark:border-zinc-800 text-[11px] text-zinc-400">
                Alumnos registrados en el plantel
            </div>
        </flux:card>

        <!-- 2. Familias / Representantes -->
        <flux:card class="flex flex-col justify-between space-y-3">
            <div class="flex justify-between items-start">
                <span class="text-sm font-medium text-zinc-500">Representantes</span>
                <div class="p-2 bg-emerald-50 dark:bg-emerald-950/50 rounded-lg text-emerald-600 dark:text-emerald-400">
                    <flux:icon icon="user-group" class="w-5 h-5" />
                </div>
            </div>
            <div>
                <div class="text-3xl font-bold text-zinc-900 dark:text-zinc-100">{{ $totalRepresentantes }}</div>
                <div class="text-xs text-zinc-500 mt-1">
                    Padres, madres y tutores legales
                </div>
            </div>
            <div class="pt-2 border-t border-zinc-100 dark:border-zinc-800 text-[11px] text-zinc-400">
                Comunidad de padres activa
            </div>
        </flux:card>

        <!-- 3. Personal y Docentes -->
        <flux:card class="flex flex-col justify-between space-y-3">
            <div class="flex justify-between items-start">
                <span class="text-sm font-medium text-zinc-500">Personal / Nómina</span>
                <div class="p-2 bg-indigo-50 dark:bg-indigo-950/50 rounded-lg text-indigo-600 dark:text-indigo-400">
                    <flux:icon icon="briefcase" class="w-5 h-5" />
                </div>
            </div>
            <div>
                <div class="text-3xl font-bold text-zinc-900 dark:text-zinc-100">{{ $totalTrabajadores }}</div>
                <div class="text-xs text-zinc-500 mt-1 flex items-center gap-2">
                    <span class="text-emerald-600 font-medium">{{ $docentesActivos }} activos</span>
                    @if ($docentesReposo > 0)
                        <span>·</span>
                        <span class="text-amber-600 font-medium">{{ $docentesReposo }} en reposo</span>
                    @endif
                </div>
            </div>
            <div class="pt-2 border-t border-zinc-100 dark:border-zinc-800 text-[11px] text-zinc-400">
                Docentes, directivos y obreros
            </div>
        </flux:card>

        <!-- 4. Ratio Alumnos por Docente -->
        <flux:card class="flex flex-col justify-between space-y-3">
            <div class="flex justify-between items-start">
                <span class="text-sm font-medium text-zinc-500">Ratio Pedagógico</span>
                <div class="p-2 bg-amber-50 dark:bg-amber-950/50 rounded-lg text-amber-600 dark:text-amber-400">
                    <flux:icon icon="chart-bar" class="w-5 h-5" />
                </div>
            </div>
            <div>
                <div class="text-3xl font-bold text-zinc-900 dark:text-zinc-100">{{ $ratioDocente }}</div>
                <div class="text-xs text-zinc-500 mt-1">
                    Alumnos por docente en aula
                </div>
            </div>
            <div class="pt-2 border-t border-zinc-100 dark:border-zinc-800 text-[11px] text-zinc-400">
                Atención personalizada
            </div>
        </flux:card>
    </div>

    <!-- Sección Principal: Dos Columnas -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Columna Izquierda (2/3): Últimos Alumnos Inscritos -->
        <div class="lg:col-span-2 space-y-4">
            <flux:card class="overflow-hidden p-0">
                <div class="p-4 border-b border-zinc-200 dark:border-zinc-700 flex justify-between items-center">
                    <div>
                        <flux:heading size="lg">Alumnos Recientemente Inscritos</flux:heading>
                        <flux:subheading>Últimos ingresos formalizados en el preescolar</flux:subheading>
                    </div>
                    <flux:button size="sm" variant="ghost" icon-trailing="arrow-right" href="{{ route('alumnos.index') }}" wire:navigate>
                        Ver todos
                    </flux:button>
                </div>

                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Alumno</flux:table.column>
                        <flux:table.column>Cédula Escolar</flux:table.column>
                        <flux:table.column>Edad</flux:table.column>
                        <flux:table.column>Representante Legal</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @forelse ($ultimosAlumnos as $alumno)
                            @php
                                $repLegal = $alumno->representantes->where('pivot.representante_legal', true)->first() ?? $alumno->representantes->first();
                            @endphp
                            <flux:table.row :key="$alumno->id">
                                <flux:table.cell class="flex items-center gap-3">
                                    <flux:avatar 
                                        size="sm"
                                        :initials="substr($alumno->persona->nombre, 0, 1) . substr($alumno->persona->apellido, 0, 1)" 
                                        color="{{ $alumno->persona->genero === 'F' ? 'pink' : 'blue' }}"
                                    />
                                    <div>
                                        <div class="font-medium text-sm text-zinc-900 dark:text-zinc-100">
                                            {{ $alumno->persona->nombre }} {{ $alumno->persona->apellido }}
                                        </div>
                                        <div class="text-[11px] text-zinc-400">
                                            Ingreso: {{ $alumno->fecha_ingreso->format('d/m/Y') }}
                                        </div>
                                    </div>
                                </flux:table.cell>

                                <flux:table.cell class="font-mono text-xs font-semibold">
                                    {{ $alumno->persona->prefijo }}-{{ $alumno->persona->cedula }}
                                </flux:table.cell>

                                <flux:table.cell>
                                    <flux:badge size="sm" color="zinc">
                                        {{ $alumno->persona->fecha_nacimiento->age }} años
                                    </flux:badge>
                                </flux:table.cell>

                                <flux:table.cell class="text-xs">
                                    @if ($repLegal)
                                        <span class="font-medium text-zinc-800 dark:text-zinc-200">
                                            {{ $repLegal->persona->nombre }} {{ $repLegal->persona->apellido }}
                                        </span>
                                        <span class="text-zinc-400 block text-[10px]">
                                            ({{ $repLegal->pivot->parentesco }})
                                        </span>
                                    @else
                                        <span class="text-zinc-400 italic">Sin representante</span>
                                    @endif
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="4" class="text-center py-6 text-zinc-500">
                                    No hay alumnos inscritos aún.
                                </flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>
            </flux:card>
        </div>

        <!-- Columna Derecha (1/3): Distribución por Cargos y Alertas -->
        <div class="space-y-4">
            <!-- Cargos y Personal -->
            <flux:card class="space-y-4">
                <div class="flex justify-between items-center border-b border-zinc-200 dark:border-zinc-700 pb-3">
                    <div>
                        <flux:heading size="md">Plantilla por Cargo</flux:heading>
                        <flux:subheading class="text-xs">Distribución activa del personal</flux:subheading>
                    </div>
                    <flux:button size="sm" variant="ghost" icon="briefcase" href="{{ route('trabajadores.index') }}" wire:navigate />
                </div>

                <div class="space-y-3">
                    @forelse ($cargosConteo as $cargo)
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-zinc-700 dark:text-zinc-300 font-medium truncate">
                                {{ $cargo->nombre_cargo }}
                            </span>
                            <flux:badge size="sm" color="{{ $cargo->trabajadores_count > 0 ? 'blue' : 'zinc' }}">
                                {{ $cargo->trabajadores_count }} {{ Str::plural('persona', $cargo->trabajadores_count) }}
                            </flux:badge>
                        </div>
                    @empty
                        <div class="text-xs text-zinc-400 italic">No hay cargos configurados.</div>
                    @endforelse
                </div>
            </flux:card>

            <!-- Alerta de Personal en Reposo (si aplica) -->
            @if ($personalEnReposo->count() > 0)
                <flux:card class="border-amber-200 dark:border-amber-900/50 bg-amber-50/50 dark:bg-amber-950/20 space-y-3">
                    <div class="flex items-center gap-2 text-amber-700 dark:text-amber-400 font-semibold text-sm">
                        <flux:icon icon="clock" class="w-4 h-4" />
                        Personal en Reposo Médico ({{ $personalEnReposo->count() }})
                    </div>
                    <div class="text-xs text-amber-800 dark:text-amber-300 space-y-1">
                        @foreach ($personalEnReposo as $personal)
                            <div class="flex justify-between items-center py-1 border-b border-amber-200/50 dark:border-amber-900/30 last:border-0">
                                <span class="font-medium">{{ $personal->persona->nombre }} {{ $personal->persona->apellido }}</span>
                                <span class="text-[11px] text-amber-600 dark:text-amber-400">{{ $personal->cargos->first()?->nombre_cargo ?? 'Personal' }}</span>
                            </div>
                        @endforeach
                    </div>
                </flux:card>
            @endif

            <!-- Tarjeta Informativa de Cédula Escolar MPPE -->
            <flux:card class="border-zinc-200 dark:border-zinc-800 space-y-2 bg-zinc-50/50 dark:bg-zinc-900">
                <div class="flex items-center gap-2 text-zinc-700 dark:text-zinc-300 font-semibold text-xs uppercase tracking-wider">
                    <flux:icon icon="information-circle" class="w-4 h-4 text-blue-500" />
                    Normativa MPPE
                </div>
                <div class="text-xs text-zinc-600 dark:text-zinc-400 leading-relaxed">
                    Las Cédulas Escolares generadas siguen el formato oficial de 11 dígitos: <code>[Parto] + [Año Nacimiento] + [Cédula Representante]</code>.
                </div>
            </flux:card>
        </div>
    </div>
</div>

