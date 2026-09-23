<div class="flex flex-col gap-6">
    <!-- Encabezado, Buscador y Botón Crear -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <flux:heading size="xl" level="1">Listado de Alumnos</flux:heading>
            <flux:subheading>Gestiona los alumnos inscritos, sus fichas médicas y sus representantes</flux:subheading>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full sm:w-auto">
            <div class="w-full sm:w-72">
                <flux:input 
                    wire:model.live.debounce.300ms="search" 
                    placeholder="Buscar por nombre o cédula..." 
                    icon="magnifying-glass" 
                    clearable 
                />
            </div>

            <flux:button variant="outline" icon="arrow-down-tray" href="{{ route('alumnos.export.csv') }}">
                Exportar Matrícula
            </flux:button>

            <flux:button variant="primary" icon="user-plus" wire:click="openCreateModal">
                Inscribir Alumno
            </flux:button>
        </div>
    </div>

    <!-- Tabla con Flux UI -->
    <flux:card class="overflow-hidden p-0">
        <flux:table :paginate="$alumnos">
            <flux:table.columns>
                <flux:table.column>Alumno</flux:table.column>
                <flux:table.column>Cédula Escolar</flux:table.column>
                <flux:table.column>Edad</flux:table.column>
                <flux:table.column>Talla / Peso</flux:table.column>
                <flux:table.column>Sangre</flux:table.column>
                <flux:table.column>Representante Legal</flux:table.column>
                <flux:table.column align="end">Acciones</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($alumnos as $alumno)
                    <flux:table.row :key="$alumno->id">
                        <!-- Alumno -->
                        <flux:table.cell class="flex items-center gap-3">
                            <flux:avatar 
                                :initials="substr($alumno->persona->nombre, 0, 1) . substr($alumno->persona->apellido, 0, 1)" 
                                color="{{ $alumno->persona->genero === 'M' ? 'blue' : 'pink' }}"
                            />
                            <div>
                                <span class="font-medium text-zinc-900 dark:text-zinc-100">
                                    {{ $alumno->persona->nombre }} {{ $alumno->persona->apellido }}
                                </span>
                                <div class="text-xs text-zinc-500">
                                    Ingreso: {{ $alumno->fecha_ingreso->format('d/m/Y') }}
                                </div>
                            </div>
                        </flux:table.cell>

                        <!-- Cédula -->
                        <flux:table.cell class="font-mono text-sm font-semibold">
                            {{ $alumno->persona->prefijo }}-{{ $alumno->persona->cedula }}
                        </flux:table.cell>

                        <!-- Edad -->
                        <flux:table.cell>
                            <flux:badge size="sm" color="zinc">
                                {{ $alumno->persona->fecha_nacimiento->age }} años
                            </flux:badge>
                        </flux:table.cell>

                        <!-- Talla / Peso -->
                        <flux:table.cell class="text-xs">
                            C: {{ $alumno->talla_camisa ?? '-' }} | P: {{ $alumno->talla_pantalon ?? '-' }}
                            @if($alumno->peso)
                                <div class="text-zinc-500 font-medium">{{ $alumno->peso }} kg</div>
                            @endif
                        </flux:table.cell>

                        <!-- Sangre -->
                        <flux:table.cell>
                            <flux:badge size="sm" color="red" inset="top bottom">
                                {{ $alumno->tipo_sangre }}
                            </flux:badge>
                        </flux:table.cell>

                        <!-- Representante Legal -->
                        <flux:table.cell>
                            @php
                                $representanteLegal = $alumno->representantes->firstWhere('pivot.representante_legal', 1);
                            @endphp

                            @if ($representanteLegal)
                                <div class="text-sm font-medium">
                                    {{ $representanteLegal->persona->nombre }} {{ $representanteLegal->persona->apellido }}
                                </div>
                                <div class="text-xs text-zinc-500">
                                    {{ $representanteLegal->pivot->parentesco }} ({{ $representanteLegal->persona->codigo_tlf }}-{{ $representanteLegal->persona->numero_tlf }})
                                </div>
                            @else
                                <span class="text-xs text-zinc-400 italic">Sin representante</span>
                            @endif
                        </flux:table.cell>

                        <!-- BOTONES DE ACCIÓN -->
                        <flux:table.cell align="end">
                            <div class="flex items-center justify-end gap-1">
                                <!-- Ver Ficha -->
                                <flux:button size="sm" variant="ghost" icon="eye" wire:click="showAlumno({{ $alumno->id }})" title="Ver Ficha Médica y Expediente" />
                                
                                <!-- Imprimir Planilla -->
                                <flux:button size="sm" variant="ghost" icon="printer" href="{{ route('alumnos.ficha', $alumno->id) }}" target="_blank" title="Imprimir Planilla Oficial" />

                                <!-- Editar -->
                                <flux:button size="sm" variant="ghost" icon="pencil-square" wire:click="editAlumno({{ $alumno->id }})" title="Editar datos" />
                                
                                <!-- Eliminar -->
                                <flux:button size="sm" variant="ghost" icon="trash" class="text-red-600 hover:text-red-700" wire:click="confirmDelete({{ $alumno->id }})" title="Retirar alumno" />
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="7" class="text-center py-8 text-zinc-500">
                            No se encontraron alumnos que coincidan con "{{ $search }}".
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>

    <!-- 1. MODAL: VER FICHA / EXPEDIENTE -->
    <flux:modal name="ver-alumno" class="w-full max-w-xl">
        @if ($alumnoSeleccionado)
            <div class="space-y-6">
                <!-- Encabezado con Avatar Grande -->
                <div class="flex items-center gap-4 border-b border-zinc-200 dark:border-zinc-700 pb-4">
                    <flux:avatar 
                        size="lg"
                        :initials="substr($alumnoSeleccionado->persona->nombre, 0, 1) . substr($alumnoSeleccionado->persona->apellido, 0, 1)" 
                        color="{{ $alumnoSeleccionado->persona->genero === 'M' ? 'blue' : 'pink' }}"
                    />
                    <div>
                        <flux:heading size="xl">{{ $alumnoSeleccionado->persona->nombre }} {{ $alumnoSeleccionado->persona->apellido }}</flux:heading>
                        <div class="text-sm font-mono font-medium text-zinc-600 dark:text-zinc-400">
                            Cédula Escolar: {{ $alumnoSeleccionado->persona->prefijo }}-{{ $alumnoSeleccionado->persona->cedula }}
                        </div>
                    </div>
                </div>

                <!-- Datos Médicos y Físicos -->
                <div>
                    <flux:heading size="sm" class="mb-2 text-zinc-700 dark:text-zinc-300">Ficha Médica y Medidas</flux:heading>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div class="p-3 rounded-lg bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700">
                            <div class="text-xs text-zinc-500">Tipo Sangre</div>
                            <div class="text-base font-bold text-red-600">{{ $alumnoSeleccionado->tipo_sangre }}</div>
                        </div>
                        <div class="p-3 rounded-lg bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700">
                            <div class="text-xs text-zinc-500">Edad</div>
                            <div class="text-base font-bold">{{ $alumnoSeleccionado->persona->fecha_nacimiento->age }} años</div>
                        </div>
                        <div class="p-3 rounded-lg bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700">
                            <div class="text-xs text-zinc-500">Peso / Estatura</div>
                            <div class="text-sm font-semibold">{{ $alumnoSeleccionado->peso ?? '-' }} kg / {{ $alumnoSeleccionado->estatura ?? '-' }} m</div>
                        </div>
                        <div class="p-3 rounded-lg bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700">
                            <div class="text-xs text-zinc-500">Tallas (C / P)</div>
                            <div class="text-sm font-semibold">{{ $alumnoSeleccionado->talla_camisa ?? '-' }} / {{ $alumnoSeleccionado->talla_pantalon ?? '-' }}</div>
                        </div>
                    </div>
                </div>

                <!-- Datos de Nacimiento e Ingreso -->
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <span class="text-zinc-500">Fecha de Nacimiento:</span>
                        <div class="font-medium">{{ $alumnoSeleccionado->persona->fecha_nacimiento->format('d/m/Y') }}</div>
                    </div>
                    <div>
                        <span class="text-zinc-500">Lugar de Nacimiento:</span>
                        <div class="font-medium">{{ $alumnoSeleccionado->lugar_nacimiento }}</div>
                    </div>
                    <div>
                        <span class="text-zinc-500">Fecha de Ingreso:</span>
                        <div class="font-medium">{{ $alumnoSeleccionado->fecha_ingreso->format('d/m/Y') }}</div>
                    </div>
                    <div>
                        <span class="text-zinc-500">Dirección:</span>
                        <div class="font-medium">{{ $alumnoSeleccionado->persona->direccion }}</div>
                    </div>
                </div>

                <!-- Representante Asignado -->
                @php
                    $repLegal = $alumnoSeleccionado->representantes->firstWhere('pivot.representante_legal', 1);
                @endphp
                <div class="p-4 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-900">
                    <flux:heading size="sm" class="mb-1">Representante Legal (Contacto de Emergencia)</flux:heading>
                    @if ($repLegal)
                        <div class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                            {{ $repLegal->persona->nombre }} {{ $repLegal->persona->apellido }} ({{ $repLegal->pivot->parentesco }})
                        </div>
                        <div class="text-xs text-zinc-500 mt-1">
                            Cédula: {{ $repLegal->persona->prefijo }}-{{ $repLegal->persona->cedula }} | Teléfono: {{ $repLegal->persona->codigo_tlf }}-{{ $repLegal->persona->numero_tlf }}
                        </div>
                        <div class="text-xs text-zinc-500">
                            Email: {{ $repLegal->persona->email }}
                        </div>
                    @else
                        <div class="text-xs text-zinc-400 italic">No tiene representante asignado.</div>
                    @endif
                </div>

                <div class="flex justify-between items-center gap-3">
                    <flux:button variant="filled" icon="printer" href="{{ route('alumnos.ficha', $alumnoSeleccionado->id) }}" target="_blank">
                        Imprimir Planilla Oficial
                    </flux:button>

                    <flux:modal.close>
                        <flux:button variant="primary">Cerrar</flux:button>
                    </flux:modal.close>
                </div>
            </div>
        @endif
    </flux:modal>

    <!-- 2. MODAL: INSCRIBIR NUEVO ALUMNO -->
    <flux:modal name="crear-alumno" class="w-full max-w-2xl max-h-[85vh] overflow-y-auto">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">Inscribir Nuevo Alumno</flux:heading>
                <flux:subheading>Completa los datos del niño. La Cédula Escolar se calcula automáticamente.</flux:subheading>
            </div>

            <!-- 1. Representante Legal -->
            <div class="space-y-3">
                <flux:separator text="1. Representante Legal" />
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <flux:field class="sm:col-span-2">
                        <flux:label>Selecciona al Representante</flux:label>
                        <flux:select wire:model.live="representante_id">
                            <flux:select.option value="">-- Selecciona para autocalcular cédula --</flux:select.option>
                            @foreach ($representantes as $rep)
                                <flux:select.option value="{{ $rep->id }}">
                                    {{ $rep->persona->nombre }} {{ $rep->persona->apellido }} ({{ $rep->persona->prefijo }}-{{ $rep->persona->cedula }})
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="representante_id" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Parentesco</flux:label>
                        <flux:select wire:model="parentesco">
                            <flux:select.option value="Madre">Madre</flux:select.option>
                            <flux:select.option value="Padre">Padre</flux:select.option>
                            <flux:select.option value="Abuela">Abuela</flux:select.option>
                            <flux:select.option value="Abuelo">Abuelo</flux:select.option>
                            <flux:select.option value="Tía">Tía</flux:select.option>
                            <flux:select.option value="Tío">Tío</flux:select.option>
                            <flux:select.option value="Tutor Legal">Tutor Legal</flux:select.option>
                        </flux:select>
                        <flux:error name="parentesco" />
                    </flux:field>
                </div>
            </div>

            <!-- 2. Datos de Identificación -->
            <div class="space-y-3">
                <flux:separator text="2. Datos de Identificación del Niño" />

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <flux:field>
                        <flux:label>Fecha de Nacimiento</flux:label>
                        <flux:input type="date" wire:model.live="fecha_nacimiento" />
                        <flux:error name="fecha_nacimiento" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Tipo de Parto / Orden del Año</flux:label>
                        <flux:select wire:model.live="parto">
                            <flux:select.option value="1">1 - Sencillo / 1er morocho</flux:select.option>
                            <flux:select.option value="2">2 - 2do morocho / 2do del año</flux:select.option>
                            <flux:select.option value="3">3 - 3er trillizo / 3ero del año</flux:select.option>
                        </flux:select>
                        <flux:error name="parto" />
                    </flux:field>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <flux:field>
                        <flux:label>Prefijo</flux:label>
                        <flux:select wire:model="prefijo">
                            <flux:select.option value="V">V (Venezolano)</flux:select.option>
                            <flux:select.option value="E">E (Extranjero)</flux:select.option>
                        </flux:select>
                        <flux:error name="prefijo" />
                    </flux:field>

                    <flux:field class="sm:col-span-2">
                        <flux:label>Cédula Escolar (11 dígitos oficiales)</flux:label>
                        <flux:input wire:model="cedula" placeholder="Se genera automáticamente..." />
                        <flux:description>Parto (1d) + Año (2d) + Cédula Rep. (8d)</flux:description>
                        <flux:error name="cedula" />
                    </flux:field>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <flux:field>
                        <flux:label>Nombres</flux:label>
                        <flux:input wire:model="nombre" placeholder="Ej: Lucas Gabriel" />
                        <flux:error name="nombre" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Apellidos</flux:label>
                        <flux:input wire:model="apellido" placeholder="Ej: Mendoza Peña" />
                        <flux:error name="apellido" />
                    </flux:field>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <flux:field>
                        <flux:label>Género</flux:label>
                        <flux:select wire:model="genero">
                            <flux:select.option value="M">Masculino</flux:select.option>
                            <flux:select.option value="F">Femenino</flux:select.option>
                        </flux:select>
                        <flux:error name="genero" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Correo Electrónico (Contacto)</flux:label>
                        <flux:input type="email" wire:model="email" placeholder="contacto@ejemplo.com" />
                        <flux:error name="email" />
                    </flux:field>
                </div>

                <div class="grid grid-cols-3 gap-2">
                    <flux:field>
                        <flux:label>Cód. Tlf</flux:label>
                        <flux:select wire:model="codigo_tlf">
                            <flux:select.option value="0414">0414</flux:select.option>
                            <flux:select.option value="0424">0424</flux:select.option>
                            <flux:select.option value="0412">0412</flux:select.option>
                            <flux:select.option value="0416">0416</flux:select.option>
                            <flux:select.option value="0426">0426</flux:select.option>
                        </flux:select>
                        <flux:error name="codigo_tlf" />
                    </flux:field>

                    <flux:field class="col-span-2">
                        <flux:label>Número de Teléfono</flux:label>
                        <flux:input wire:model="numero_tlf" placeholder="1234567" maxlength="7" />
                        <flux:error name="numero_tlf" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Dirección de Habitación</flux:label>
                    <flux:textarea wire:model="direccion" rows="2" placeholder="Sector, calle, casa..." />
                    <flux:error name="direccion" />
                </flux:field>
            </div>

            <!-- 3. Ficha Escolar y Salud -->
            <div class="space-y-3">
                <flux:separator text="3. Ficha Escolar y Salud" />

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <flux:field>
                        <flux:label>Fecha Ingreso</flux:label>
                        <flux:input type="date" wire:model="fecha_ingreso" />
                        <flux:error name="fecha_ingreso" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Lugar Nacimiento</flux:label>
                        <flux:input wire:model="lugar_nacimiento" placeholder="Ej: Caracas" />
                        <flux:error name="lugar_nacimiento" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Tipo de Sangre</flux:label>
                        <flux:select wire:model="tipo_sangre">
                            <flux:select.option value="O+">O+</flux:select.option>
                            <flux:select.option value="O-">O-</flux:select.option>
                            <flux:select.option value="A+">A+</flux:select.option>
                            <flux:select.option value="A-">A-</flux:select.option>
                            <flux:select.option value="B+">B+</flux:select.option>
                            <flux:select.option value="B-">B-</flux:select.option>
                            <flux:select.option value="AB+">AB+</flux:select.option>
                            <flux:select.option value="AB-">AB-</flux:select.option>
                        </flux:select>
                        <flux:error name="tipo_sangre" />
                    </flux:field>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <flux:field>
                        <flux:label>Talla Camisa</flux:label>
                        <flux:input wire:model="talla_camisa" placeholder="4, 6..." />
                    </flux:field>
                    <flux:field>
                        <flux:label>Talla Pantalón</flux:label>
                        <flux:input wire:model="talla_pantalon" placeholder="4, 6..." />
                    </flux:field>
                    <flux:field>
                        <flux:label>Peso (Kg)</flux:label>
                        <flux:input type="number" step="0.1" wire:model="peso" placeholder="18.5" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Estatura (m)</flux:label>
                        <flux:input type="number" step="0.01" wire:model="estatura" placeholder="1.05" />
                    </flux:field>
                </div>
            </div>

            <!-- Botones -->
            <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Guardar Alumno</flux:button>
            </div>
        </form>
    </flux:modal>

    <!-- 3. MODAL: EDITAR ALUMNO -->
    <flux:modal name="editar-alumno" class="w-full max-w-2xl max-h-[85vh] overflow-y-auto">
        <form wire:submit="update" class="space-y-6">
            <div>
                <flux:heading size="lg">Editar Datos del Alumno</flux:heading>
                <flux:subheading>Actualiza las medidas físicas, tallas o datos de contacto del alumno.</flux:subheading>
            </div>

            <div class="space-y-4">
                <flux:separator text="Datos de Identificación" />
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <flux:field>
                        <flux:label>Nombres</flux:label>
                        <flux:input wire:model="nombre" />
                        <flux:error name="nombre" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Apellidos</flux:label>
                        <flux:input wire:model="apellido" />
                        <flux:error name="apellido" />
                    </flux:field>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <flux:field>
                        <flux:label>Cédula Escolar</flux:label>
                        <flux:input wire:model="cedula" />
                        <flux:error name="cedula" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Fecha de Nacimiento</flux:label>
                        <flux:input type="date" wire:model="fecha_nacimiento" />
                        <flux:error name="fecha_nacimiento" />
                    </flux:field>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <flux:field>
                        <flux:label>Correo Electrónico</flux:label>
                        <flux:input type="email" wire:model="email" />
                        <flux:error name="email" />
                    </flux:field>

                    <div class="grid grid-cols-3 gap-2">
                        <flux:field>
                            <flux:label>Cód.</flux:label>
                            <flux:select wire:model="codigo_tlf">
                                <flux:select.option value="0414">0414</flux:select.option>
                                <flux:select.option value="0424">0424</flux:select.option>
                                <flux:select.option value="0412">0412</flux:select.option>
                                <flux:select.option value="0416">0416</flux:select.option>
                                <flux:select.option value="0426">0426</flux:select.option>
                            </flux:select>
                            <flux:error name="codigo_tlf" />
                        </flux:field>
                        <flux:field class="col-span-2">
                            <flux:label>Teléfono</flux:label>
                            <flux:input wire:model="numero_tlf" maxlength="7" />
                            <flux:error name="numero_tlf" />
                        </flux:field>
                    </div>
                </div>

                <flux:field>
                    <flux:label>Dirección</flux:label>
                    <flux:textarea wire:model="direccion" rows="2" />
                    <flux:error name="direccion" />
                </flux:field>

                <flux:separator text="Medidas Físicas y Salud" />
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <flux:field>
                        <flux:label>Tipo de Sangre</flux:label>
                        <flux:select wire:model="tipo_sangre">
                            <flux:select.option value="O+">O+</flux:select.option>
                            <flux:select.option value="O-">O-</flux:select.option>
                            <flux:select.option value="A+">A+</flux:select.option>
                            <flux:select.option value="A-">A-</flux:select.option>
                            <flux:select.option value="B+">B+</flux:select.option>
                            <flux:select.option value="B-">B-</flux:select.option>
                            <flux:select.option value="AB+">AB+</flux:select.option>
                            <flux:select.option value="AB-">AB-</flux:select.option>
                        </flux:select>
                    </flux:field>
                    <flux:field>
                        <flux:label>Peso (Kg)</flux:label>
                        <flux:input type="number" step="0.1" wire:model="peso" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Estatura (m)</flux:label>
                        <flux:input type="number" step="0.01" wire:model="estatura" />
                    </flux:field>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <flux:field>
                        <flux:label>Talla Camisa</flux:label>
                        <flux:input wire:model="talla_camisa" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Talla Pantalón</flux:label>
                        <flux:input wire:model="talla_pantalon" />
                    </flux:field>
                </div>

                <flux:separator text="Representante Asignado" />
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <flux:field>
                        <flux:label>Representante</flux:label>
                        <flux:select wire:model="representante_id">
                            <flux:select.option value="">-- Sin representante --</flux:select.option>
                            @foreach ($representantes as $rep)
                                <flux:select.option value="{{ $rep->id }}">
                                    {{ $rep->persona->nombre }} {{ $rep->persona->apellido }} ({{ $rep->persona->prefijo }}-{{ $rep->persona->cedula }})
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                    </flux:field>

                    <flux:field>
                        <flux:label>Parentesco</flux:label>
                        <flux:select wire:model="parentesco">
                            <flux:select.option value="Madre">Madre</flux:select.option>
                            <flux:select.option value="Padre">Padre</flux:select.option>
                            <flux:select.option value="Abuela">Abuela</flux:select.option>
                            <flux:select.option value="Abuelo">Abuelo</flux:select.option>
                            <flux:select.option value="Tía">Tía</flux:select.option>
                            <flux:select.option value="Tío">Tío</flux:select.option>
                            <flux:select.option value="Tutor Legal">Tutor Legal</flux:select.option>
                        </flux:select>
                    </flux:field>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Guardar Cambios</flux:button>
            </div>
        </form>
    </flux:modal>

    <!-- 4. MODAL: ELIMINAR / RETIRAR ALUMNO -->
    <flux:modal name="eliminar-alumno" class="w-full max-w-md">
        @if ($alumnoSeleccionado)
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg" class="text-red-600 dark:text-red-500">¿Retirar Alumno del Plantel?</flux:heading>
                    <flux:subheading class="mt-2">
                        Estás a punto de retirar al alumno <strong>{{ $alumnoSeleccionado->persona->nombre }} {{ $alumnoSeleccionado->persona->apellido }}</strong> ({{ $alumnoSeleccionado->persona->prefijo }}-{{ $alumnoSeleccionado->persona->cedula }}).
                        Se eliminará su ficha médica y sus vinculaciones. Esta acción no se puede deshacer.
                    </flux:subheading>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                    <flux:modal.close>
                        <flux:button variant="ghost">Cancelar</flux:button>
                    </flux:modal.close>
                    <flux:button variant="danger" wire:click="delete">Sí, Retirar Alumno</flux:button>
                </div>
            </div>
        @endif
    </flux:modal>
</div>