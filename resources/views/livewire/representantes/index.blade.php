<div class="flex flex-col gap-6">
    <!-- Encabezado y Barra de Búsqueda -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <flux:heading size="xl" level="1">Listado de Representantes</flux:heading>
            <flux:subheading>Padres, madres y tutores legales de los alumnos del preescolar</flux:subheading>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full sm:w-auto">
            <div class="w-full sm:w-72">
                <flux:input 
                    wire:model.live.debounce.300ms="search" 
                    placeholder="Buscar por nombre, cédula u oficio..." 
                    icon="magnifying-glass" 
                    clearable 
                />
            </div>

            <flux:button variant="primary" icon="user-plus" wire:click="openCreateModal">
                Nuevo Representante
            </flux:button>
        </div>
    </div>

    <!-- Tabla con Flux UI -->
    <flux:card class="overflow-hidden p-0">
        <flux:table :paginate="$representantes">
            <flux:table.columns>
                <flux:table.column>Representante</flux:table.column>
                <flux:table.column>Cédula</flux:table.column>
                <flux:table.column>Ocupación / Oficio</flux:table.column>
                <flux:table.column>Teléfono</flux:table.column>
                <flux:table.column>Alumnos a Cargo</flux:table.column>
                <flux:table.column align="end">Acciones</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($representantes as $rep)
                    <flux:table.row :key="$rep->id">
                        <!-- Nombre del Representante -->
                        <flux:table.cell class="flex items-center gap-3">
                            <flux:avatar 
                                :initials="substr($rep->persona->nombre, 0, 1) . substr($rep->persona->apellido, 0, 1)" 
                                color="{{ $rep->persona->genero === 'F' ? 'pink' : 'blue' }}"
                            />
                            <div>
                                <span class="font-medium text-zinc-900 dark:text-zinc-100">
                                    {{ $rep->persona->nombre }} {{ $rep->persona->apellido }}
                                </span>
                                <div class="text-xs text-zinc-500">
                                    {{ $rep->persona->email }}
                                </div>
                            </div>
                        </flux:table.cell>

                        <!-- Cédula -->
                        <flux:table.cell class="font-mono text-sm font-semibold">
                            {{ $rep->persona->prefijo }}-{{ $rep->persona->cedula }}
                        </flux:table.cell>

                        <!-- Ocupación -->
                        <flux:table.cell>
                            <flux:badge size="sm" color="zinc">
                                {{ $rep->ocupacion }}
                            </flux:badge>
                        </flux:table.cell>

                        <!-- Teléfono -->
                        <flux:table.cell class="text-sm">
                            {{ $rep->persona->codigo_tlf }}-{{ $rep->persona->numero_tlf }}
                        </flux:table.cell>

                        <!-- Alumnos a Cargo (Muchos a Muchos) -->
                        <flux:table.cell>
                            <div class="flex flex-wrap gap-1">
                                @forelse ($rep->alumnos as $hijo)
                                    <flux:badge size="sm" color="blue" icon="academic-cap">
                                        {{ $hijo->persona->nombre }} ({{ $hijo->pivot->parentesco }})
                                    </flux:badge>
                                @empty
                                    <span class="text-xs text-zinc-400 italic">Sin alumnos asignados</span>
                                @endforelse
                            </div>
                        </flux:table.cell>

                        <!-- Acciones -->
                        <flux:table.cell align="end">
                            <div class="flex items-center justify-end gap-1">
                                <flux:button size="sm" variant="ghost" icon="eye" wire:click="showRepresentante({{ $rep->id }})" title="Ver ficha familiar" />
                                <flux:button size="sm" variant="ghost" icon="pencil-square" wire:click="editRepresentante({{ $rep->id }})" title="Editar datos" />
                                <flux:button size="sm" variant="ghost" icon="trash" class="text-red-600 hover:text-red-700" wire:click="confirmDelete({{ $rep->id }})" title="Eliminar" />
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6" class="text-center py-8 text-zinc-500">
                            No se encontraron representantes que coincidan con "{{ $search }}".
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>

    <!-- 1. MODAL: VER FICHA DEL REPRESENTANTE Y FAMILIA -->
    <flux:modal name="ver-representante" class="w-full max-w-lg">
        @if ($representanteSeleccionado)
            <div class="space-y-6">
                <div class="flex items-center gap-4 border-b border-zinc-200 dark:border-zinc-700 pb-4">
                    <flux:avatar 
                        size="lg"
                        :initials="substr($representanteSeleccionado->persona->nombre, 0, 1) . substr($representanteSeleccionado->persona->apellido, 0, 1)" 
                        color="{{ $representanteSeleccionado->persona->genero === 'F' ? 'pink' : 'blue' }}"
                    />
                    <div>
                        <flux:heading size="xl">{{ $representanteSeleccionado->persona->nombre }} {{ $representanteSeleccionado->persona->apellido }}</flux:heading>
                        <div class="text-sm text-zinc-500">
                            {{ $representanteSeleccionado->ocupacion }} | {{ $representanteSeleccionado->persona->prefijo }}-{{ $representanteSeleccionado->persona->cedula }}
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <span class="text-zinc-500">Teléfono:</span>
                        <div class="font-medium">{{ $representanteSeleccionado->persona->codigo_tlf }}-{{ $representanteSeleccionado->persona->numero_tlf }}</div>
                    </div>
                    <div>
                        <span class="text-zinc-500">Correo Electrónico:</span>
                        <div class="font-medium">{{ $representanteSeleccionado->persona->email }}</div>
                    </div>
                    <div class="col-span-2">
                        <span class="text-zinc-500">Dirección de Habitación:</span>
                        <div class="font-medium">{{ $representanteSeleccionado->persona->direccion }}</div>
                    </div>
                </div>

                <!-- Hijos / Alumnos a su cargo -->
                <div class="p-4 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-900">
                    <flux:heading size="sm" class="mb-3">Alumnos / Hijos Inscritos en el Plantel</flux:heading>
                    <div class="space-y-2">
                        @forelse ($representanteSeleccionado->alumnos as $hijo)
                            <div class="flex items-center justify-between p-2 rounded-lg bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">
                                <div>
                                    <div class="font-medium text-sm">{{ $hijo->persona->nombre }} {{ $hijo->persona->apellido }}</div>
                                    <div class="text-xs text-zinc-500">Cédula Escolar: {{ $hijo->persona->prefijo }}-{{ $hijo->persona->cedula }}</div>
                                </div>
                                <div class="text-right">
                                    <flux:badge size="sm" color="blue">{{ $hijo->pivot->parentesco }}</flux:badge>
                                    @if($hijo->pivot->representante_legal)
                                        <span class="text-[10px] text-green-600 font-semibold block">Legal</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-xs text-zinc-400 italic">No tiene alumnos asociados actualmente.</div>
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

    <!-- 2. MODAL: REGISTRAR NUEVO REPRESENTANTE -->
    <flux:modal name="crear-representante" class="w-full max-w-xl max-h-[85vh] overflow-y-auto">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">Nuevo Representante</flux:heading>
                <flux:subheading>Registra a un padre, madre o tutor legal en el sistema.</flux:subheading>
            </div>

            <div class="space-y-4">
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
                        <flux:input wire:model="cedula" placeholder="Ej: 18555666" />
                        <flux:error name="cedula" />
                    </flux:field>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <flux:field>
                        <flux:label>Nombres</flux:label>
                        <flux:input wire:model="nombre" placeholder="Ej: Carmen Elena" />
                        <flux:error name="nombre" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Apellidos</flux:label>
                        <flux:input wire:model="apellido" placeholder="Ej: Rivas Salazar" />
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
                            <flux:select.option value="F">Femenino (Madre)</flux:select.option>
                            <flux:select.option value="M">Masculino (Padre)</flux:select.option>
                        </flux:select>
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Ocupación / Profesión</flux:label>
                    <flux:input wire:model="ocupacion" placeholder="Ej: Docente, Comerciante, Ingeniero..." />
                    <flux:error name="ocupacion" />
                </flux:field>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <flux:field>
                        <flux:label>Correo Electrónico</flux:label>
                        <flux:input type="email" wire:model="email" placeholder="correo@ejemplo.com" />
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
                    <flux:textarea wire:model="direccion" rows="2" placeholder="Sector, calle, casa..." />
                    <flux:error name="direccion" />
                </flux:field>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Guardar Representante</flux:button>
            </div>
        </form>
    </flux:modal>

    <!-- 3. MODAL: EDITAR REPRESENTANTE -->
    <flux:modal name="editar-representante" class="w-full max-w-xl max-h-[85vh] overflow-y-auto">
        <form wire:submit="update" class="space-y-6">
            <div>
                <flux:heading size="lg">Editar Representante</flux:heading>
                <flux:subheading>Actualiza los datos de contacto y ocupación del representante.</flux:subheading>
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
                        <flux:label>Ocupación</flux:label>
                        <flux:input wire:model="ocupacion" />
                        <flux:error name="ocupacion" />
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
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Guardar Cambios</flux:button>
            </div>
        </form>
    </flux:modal>

    <!-- 4. MODAL: ELIMINAR REPRESENTANTE -->
    <flux:modal name="eliminar-representante" class="w-full max-w-md">
        @if ($representanteSeleccionado)
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg" class="text-red-600 dark:text-red-500">¿Eliminar Representante?</flux:heading>
                    <flux:subheading class="mt-2">
                        Estás a punto de eliminar a <strong>{{ $representanteSeleccionado->persona->nombre }} {{ $representanteSeleccionado->persona->apellido }}</strong>.
                        @if($representanteSeleccionado->alumnos->count() > 0)
                            <div class="mt-2 p-2 rounded bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 text-xs">
                                ⚠️ Atención: Tiene {{ $representanteSeleccionado->alumnos->count() }} alumno(s) asociado(s). Se desvinculará de ellos.
                            </div>
                        @endif
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

