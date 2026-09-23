<div class="flex flex-col gap-6">
    <!-- Encabezado y Controles -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <flux:heading size="xl" level="1">Personal y Docentes</flux:heading>
            <flux:subheading>Gestión de maestras, directivos, personal administrativo y obrero</flux:subheading>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full sm:w-auto">
            <!-- Selector de Estatus -->
            <div class="w-full sm:w-44">
                <flux:select wire:model.live="estatusFilter">
                    <flux:select.option value="">Todos los Estatus</flux:select.option>
                    <flux:select.option value="Activo">Activos</flux:select.option>
                    <flux:select.option value="Reposo">En Reposo</flux:select.option>
                    <flux:select.option value="Inactivo">Inactivos</flux:select.option>
                </flux:select>
            </div>

            <!-- Búsqueda -->
            <div class="w-full sm:w-72">
                <flux:input 
                    wire:model.live.debounce.300ms="search" 
                    placeholder="Buscar por nombre, cédula o cargo..." 
                    icon="magnifying-glass" 
                    clearable 
                />
            </div>

            <flux:button variant="primary" icon="user-plus" wire:click="openCreateModal">
                Nuevo Trabajador
            </flux:button>
        </div>
    </div>

    <!-- Tabla de Personal con Flux UI -->
    <flux:card class="overflow-hidden p-0">
        <flux:table :paginate="$trabajadores">
            <flux:table.columns>
                <flux:table.column>Personal / Docente</flux:table.column>
                <flux:table.column>Cédula</flux:table.column>
                <flux:table.column>Cargo Actual</flux:table.column>
                <flux:table.column>Nivel Académico</flux:table.column>
                <flux:table.column>Estatus</flux:table.column>
                <flux:table.column>Ingreso / Antigüedad</flux:table.column>
                <flux:table.column align="end">Acciones</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($trabajadores as $trabajador)
                    <flux:table.row :key="$trabajador->id">
                        <!-- Nombre del Personal -->
                        <flux:table.cell class="flex items-center gap-3">
                            <flux:avatar 
                                :initials="substr($trabajador->persona->nombre, 0, 1) . substr($trabajador->persona->apellido, 0, 1)" 
                                color="{{ $trabajador->persona->genero === 'F' ? 'pink' : 'blue' }}"
                            />
                            <div>
                                <span class="font-medium text-zinc-900 dark:text-zinc-100">
                                    {{ $trabajador->persona->nombre }} {{ $trabajador->persona->apellido }}
                                </span>
                                <div class="text-xs text-zinc-500">
                                    {{ $trabajador->persona->email }}
                                </div>
                            </div>
                        </flux:table.cell>

                        <!-- Cédula -->
                        <flux:table.cell class="font-mono text-sm font-semibold">
                            {{ $trabajador->persona->prefijo }}-{{ $trabajador->persona->cedula }}
                        </flux:table.cell>

                        <!-- Cargo Actual -->
                        <flux:table.cell>
                            @php
                                $cargoActual = $trabajador->cargos->whereNull('pivot.fecha_fin')->first() ?? $trabajador->cargos->last();
                            @endphp
                            @if ($cargoActual)
                                <flux:badge size="sm" color="blue" icon="briefcase">
                                    {{ $cargoActual->nombre_cargo }}
                                </flux:badge>
                            @else
                                <span class="text-xs text-zinc-400 italic">Sin cargo asignado</span>
                            @endif
                        </flux:table.cell>

                        <!-- Nivel Académico -->
                        <flux:table.cell>
                            <flux:badge size="sm" color="zinc">
                                {{ $trabajador->nivel_academico }}
                            </flux:badge>
                        </flux:table.cell>

                        <!-- Estatus Laboral -->
                        <flux:table.cell>
                            @if ($trabajador->estatus_laboral === 'Activo')
                                <flux:badge size="sm" color="emerald" icon="check-circle">Activo</flux:badge>
                            @elseif ($trabajador->estatus_laboral === 'Reposo')
                                <flux:badge size="sm" color="amber" icon="clock">Reposo</flux:badge>
                            @else
                                <flux:badge size="sm" color="zinc" icon="no-symbol">Inactivo</flux:badge>
                            @endif
                        </flux:table.cell>

                        <!-- Fecha de Ingreso y Antigüedad -->
                        <flux:table.cell class="text-xs text-zinc-600 dark:text-zinc-400">
                            <div>{{ $trabajador->fecha_ingreso->format('d/m/Y') }}</div>
                            <div class="text-[11px] text-zinc-400">Hace {{ $trabajador->fecha_ingreso->diffForHumans(null, true) }}</div>
                        </flux:table.cell>

                        <!-- Acciones -->
                        <flux:table.cell align="end">
                            <div class="flex items-center justify-end gap-1">
                                <flux:button size="sm" variant="ghost" icon="eye" wire:click="showTrabajador({{ $trabajador->id }})" title="Ver ficha laboral" />
                                <flux:button size="sm" variant="ghost" icon="pencil-square" wire:click="editTrabajador({{ $trabajador->id }})" title="Editar datos" />
                                <flux:button size="sm" variant="ghost" icon="trash" class="text-red-600 hover:text-red-700" wire:click="confirmDelete({{ $trabajador->id }})" title="Eliminar" />
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="7" class="text-center py-8 text-zinc-500">
                            No se encontró personal docente o administrativo registrado.
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>

    <!-- 1. MODAL: VER FICHA LABORAL DEL TRABAJADOR -->
    <flux:modal name="ver-trabajador" class="w-full max-w-lg">
        @if ($trabajadorSeleccionado)
            <div class="space-y-6">
                <!-- Encabezado con Avatar -->
                <div class="flex items-center gap-4 border-b border-zinc-200 dark:border-zinc-700 pb-4">
                    <flux:avatar 
                        size="lg"
                        :initials="substr($trabajadorSeleccionado->persona->nombre, 0, 1) . substr($trabajadorSeleccionado->persona->apellido, 0, 1)" 
                        color="{{ $trabajadorSeleccionado->persona->genero === 'F' ? 'pink' : 'blue' }}"
                    />
                    <div>
                        <flux:heading size="xl">{{ $trabajadorSeleccionado->persona->nombre }} {{ $trabajadorSeleccionado->persona->apellido }}</flux:heading>
                        <div class="text-sm text-zinc-500">
                            {{ $trabajadorSeleccionado->persona->prefijo }}-{{ $trabajadorSeleccionado->persona->cedula }} | {{ $trabajadorSeleccionado->persona->fecha_nacimiento->age }} años
                        </div>
                    </div>
                </div>

                <!-- Datos Personales y de Contacto -->
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <span class="text-zinc-500">Teléfono:</span>
                        <div class="font-medium">{{ $trabajadorSeleccionado->persona->codigo_tlf }}-{{ $trabajadorSeleccionado->persona->numero_tlf }}</div>
                    </div>
                    <div>
                        <span class="text-zinc-500">Correo:</span>
                        <div class="font-medium">{{ $trabajadorSeleccionado->persona->email }}</div>
                    </div>
                    <div class="col-span-2">
                        <span class="text-zinc-500">Dirección:</span>
                        <div class="font-medium">{{ $trabajadorSeleccionado->persona->direccion }}</div>
                    </div>
                </div>

                <!-- Datos Laborales -->
                <div class="p-4 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-900 space-y-3">
                    <flux:heading size="sm">Ficha Institucional</flux:heading>
                    
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <span class="text-zinc-500">Fecha de Ingreso:</span>
                            <div class="font-medium">{{ $trabajadorSeleccionado->fecha_ingreso->format('d/m/Y') }} ({{ $trabajadorSeleccionado->fecha_ingreso->diffForHumans(null, true) }})</div>
                        </div>
                        <div>
                            <span class="text-zinc-500">Nivel Académico:</span>
                            <div class="font-medium">{{ $trabajadorSeleccionado->nivel_academico }}</div>
                        </div>
                        <div>
                            <span class="text-zinc-500">Estatus Laboral:</span>
                            <div>
                                @if ($trabajadorSeleccionado->estatus_laboral === 'Activo')
                                    <flux:badge size="sm" color="emerald">Activo</flux:badge>
                                @elseif ($trabajadorSeleccionado->estatus_laboral === 'Reposo')
                                    <flux:badge size="sm" color="amber">Reposo</flux:badge>
                                @else
                                    <flux:badge size="sm" color="zinc">Inactivo</flux:badge>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Historial de Cargos -->
                <div class="p-4 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-900">
                    <flux:heading size="sm" class="mb-3">Historial de Cargos Desempeñados</flux:heading>
                    <div class="space-y-2">
                        @forelse ($trabajadorSeleccionado->cargos as $cargo)
                            <div class="flex items-center justify-between p-2 rounded-lg bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">
                                <div>
                                    <div class="font-medium text-sm flex items-center gap-2">
                                        <flux:icon icon="briefcase" class="w-4 h-4 text-blue-500" />
                                        {{ $cargo->nombre_cargo }}
                                    </div>
                                    <div class="text-xs text-zinc-500">
                                        Desde: {{ date('d/m/Y', strtotime($cargo->pivot->fecha_inicio)) }}
                                        @if ($cargo->pivot->fecha_fin)
                                            - Hasta: {{ date('d/m/Y', strtotime($cargo->pivot->fecha_fin)) }}
                                        @endif
                                    </div>
                                </div>
                                <div>
                                    @if (is_null($cargo->pivot->fecha_fin))
                                        <flux:badge size="sm" color="emerald">Cargo Actual</flux:badge>
                                    @else
                                        <flux:badge size="sm" color="zinc">Finalizado</flux:badge>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-xs text-zinc-400 italic">No registra asignación de cargos.</div>
                        @endforelse
                    </div>
                </div>

                <div class="flex justify-end">
                    <flux:modal.close>
                        <flux:button variant="primary">Cerrar</flux:button>
                    </flux:modal.close>
                </div>
            </div>
        @endif
    </flux:modal>

    <!-- 2. MODAL: REGISTRAR NUEVO TRABAJADOR -->
    <flux:modal name="crear-trabajador" class="w-full max-w-2xl max-h-[85vh] overflow-y-auto">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">Nuevo Personal / Docente</flux:heading>
                <flux:subheading>Registra a una maestra, directivo, administrativo u obrero en el plantel.</flux:subheading>
            </div>

            <!-- Sección: Datos Personales -->
            <div class="space-y-4">
                <flux:heading size="sm" class="border-b border-zinc-200 dark:border-zinc-700 pb-1 text-zinc-600 dark:text-zinc-300">
                    1. Datos Personales y de Identificación
                </flux:heading>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <flux:field>
                        <flux:label>Prefijo</flux:label>
                        <flux:select wire:model="prefijo">
                            <flux:select.option value="V">V</flux:select.option>
                            <flux:select.option value="E">E</flux:select.option>
                        </flux:select>
                    </flux:field>
                    <flux:field class="sm:col-span-2">
                        <flux:label>Cédula</flux:label>
                        <flux:input wire:model="cedula" placeholder="Ej: 14289354" />
                        <flux:error name="cedula" />
                    </flux:field>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <flux:field>
                        <flux:label>Nombres</flux:label>
                        <flux:input wire:model="nombre" placeholder="Ej: María Alejandra" />
                        <flux:error name="nombre" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Apellidos</flux:label>
                        <flux:input wire:model="apellido" placeholder="Ej: Rodríguez Bello" />
                        <flux:error name="apellido" />
                    </flux:field>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <flux:field>
                        <flux:label>Fecha de Nacimiento</flux:label>
                        <flux:input type="date" wire:model="fecha_nacimiento" />
                        <flux:error name="fecha_nacimiento" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Género</flux:label>
                        <flux:select wire:model="genero">
                            <flux:select.option value="F">Femenino</flux:select.option>
                            <flux:select.option value="M">Masculino</flux:select.option>
                        </flux:select>
                        <flux:error name="genero" />
                    </flux:field>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <flux:field>
                        <flux:label>Correo Electrónico</flux:label>
                        <flux:input type="email" wire:model="email" placeholder="docente@preescolar.test" />
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
                        </flux:field>
                        <flux:field class="col-span-2">
                            <flux:label>Teléfono</flux:label>
                            <flux:input wire:model="numero_tlf" placeholder="1234567" maxlength="7" />
                            <flux:error name="numero_tlf" />
                        </flux:field>
                    </div>
                </div>

                <flux:field>
                    <flux:label>Dirección de Habitación</flux:label>
                    <flux:textarea wire:model="direccion" rows="2" placeholder="Sector, calle, número..." />
                    <flux:error name="direccion" />
                </flux:field>
            </div>

            <!-- Sección: Datos Laborales y Cargo -->
            <div class="space-y-4">
                <flux:heading size="sm" class="border-b border-zinc-200 dark:border-zinc-700 pb-1 text-zinc-600 dark:text-zinc-300">
                    2. Ficha Laboral y Asignación de Cargo
                </flux:heading>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <flux:field>
                        <flux:label>Fecha de Ingreso</flux:label>
                        <flux:input type="date" wire:model="fecha_ingreso" />
                        <flux:error name="fecha_ingreso" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Nivel Académico</flux:label>
                        <flux:select wire:model="nivel_academico">
                            <flux:select.option value="Universitario">Universitario</flux:select.option>
                            <flux:select.option value="Bachiller">Bachiller</flux:select.option>
                            <flux:select.option value="Primaria">Primaria</flux:select.option>
                        </flux:select>
                        <flux:error name="nivel_academico" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Estatus Laboral</flux:label>
                        <flux:select wire:model="estatus_laboral">
                            <flux:select.option value="Activo">Activo</flux:select.option>
                            <flux:select.option value="Reposo">En Reposo</flux:select.option>
                            <flux:select.option value="Inactivo">Inactivo</flux:select.option>
                        </flux:select>
                        <flux:error name="estatus_laboral" />
                    </flux:field>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3 rounded-lg border border-blue-200 dark:border-blue-900 bg-blue-50/40 dark:bg-blue-950/20">
                    <flux:field>
                        <flux:label>Cargo a Asignar</flux:label>
                        <flux:select wire:model="cargo_id">
                            <flux:select.option value="">Seleccione un cargo...</flux:select.option>
                            @foreach ($cargosDisponibles as $cargo)
                                <flux:select.option value="{{ $cargo->id }}">{{ $cargo->nombre_cargo }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="cargo_id" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Fecha de Inicio en el Cargo</flux:label>
                        <flux:input type="date" wire:model="fecha_inicio_cargo" />
                        <flux:error name="fecha_inicio_cargo" />
                    </flux:field>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Guardar Trabajador</flux:button>
            </div>
        </form>
    </flux:modal>

    <!-- 3. MODAL: EDITAR TRABAJADOR -->
    <flux:modal name="editar-trabajador" class="w-full max-w-2xl max-h-[85vh] overflow-y-auto">
        <form wire:submit="update" class="space-y-6">
            <div>
                <flux:heading size="lg">Editar Personal / Docente</flux:heading>
                <flux:subheading>Actualiza los datos personales, académicos, de estatus y cargo.</flux:subheading>
            </div>

            <div class="space-y-4">
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
                        <flux:label>Cédula</flux:label>
                        <flux:input wire:model="cedula" />
                        <flux:error name="cedula" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Correo Electrónico</flux:label>
                        <flux:input type="email" wire:model="email" />
                        <flux:error name="email" />
                    </flux:field>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="grid grid-cols-3 gap-2 sm:col-span-2">
                        <flux:field>
                            <flux:label>Cód.</flux:label>
                            <flux:select wire:model="codigo_tlf">
                                <flux:select.option value="0414">0414</flux:select.option>
                                <flux:select.option value="0424">0424</flux:select.option>
                                <flux:select.option value="0412">0412</flux:select.option>
                                <flux:select.option value="0416">0416</flux:select.option>
                                <flux:select.option value="0426">0426</flux:select.option>
                            </flux:select>
                        </flux:field>
                        <flux:field class="col-span-2">
                            <flux:label>Teléfono</flux:label>
                            <flux:input wire:model="numero_tlf" maxlength="7" />
                            <flux:error name="numero_tlf" />
                        </flux:field>
                    </div>

                    <flux:field>
                        <flux:label>Género</flux:label>
                        <flux:select wire:model="genero">
                            <flux:select.option value="F">Femenino</flux:select.option>
                            <flux:select.option value="M">Masculino</flux:select.option>
                        </flux:select>
                        <flux:error name="genero" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Dirección</flux:label>
                    <flux:textarea wire:model="direccion" rows="2" />
                    <flux:error name="direccion" />
                </flux:field>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-3 border-t border-zinc-200 dark:border-zinc-700">
                    <flux:field>
                        <flux:label>Fecha de Ingreso</flux:label>
                        <flux:input type="date" wire:model="fecha_ingreso" />
                        <flux:error name="fecha_ingreso" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Nivel Académico</flux:label>
                        <flux:select wire:model="nivel_academico">
                            <flux:select.option value="Universitario">Universitario</flux:select.option>
                            <flux:select.option value="Bachiller">Bachiller</flux:select.option>
                            <flux:select.option value="Primaria">Primaria</flux:select.option>
                        </flux:select>
                        <flux:error name="nivel_academico" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Estatus Laboral</flux:label>
                        <flux:select wire:model="estatus_laboral">
                            <flux:select.option value="Activo">Activo</flux:select.option>
                            <flux:select.option value="Reposo">En Reposo</flux:select.option>
                            <flux:select.option value="Inactivo">Inactivo</flux:select.option>
                        </flux:select>
                        <flux:error name="estatus_laboral" />
                    </flux:field>
                </div>

                <div class="p-3 rounded-lg border border-blue-200 dark:border-blue-900 bg-blue-50/40 dark:bg-blue-950/20">
                    <flux:field>
                        <flux:label>Cargo Asignado</flux:label>
                        <flux:select wire:model="cargo_id">
                            @foreach ($cargosDisponibles as $cargo)
                                <flux:select.option value="{{ $cargo->id }}">{{ $cargo->nombre_cargo }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="cargo_id" />
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

    <!-- 4. MODAL: ELIMINAR TRABAJADOR -->
    <flux:modal name="eliminar-trabajador" class="w-full max-w-md">
        @if ($trabajadorSeleccionado)
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg" class="text-red-600 dark:text-red-500">¿Eliminar Registro del Trabajador?</flux:heading>
                    <flux:subheading class="mt-2">
                        Estás a punto de eliminar a <strong>{{ $trabajadorSeleccionado->persona->nombre }} {{ $trabajadorSeleccionado->persona->apellido }}</strong> del sistema de nómina y personal.
                    </flux:subheading>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                    <flux:modal.close>
                        <flux:button variant="ghost">Cancelar</flux:button>
                    </flux:modal.close>
                    <flux:button variant="danger" wire:click="delete">Sí, Eliminar</flux:button>
                </div>
            </div>
        @endif
    </flux:modal>
</div>

