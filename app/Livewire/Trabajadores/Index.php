<?php

namespace App\Livewire\Trabajadores;

use App\Models\Cargo;
use App\Models\Persona;
use App\Models\Trabajador;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Personal y Docentes')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $estatusFilter = '';

    public ?int $trabajadorId = null;

    public ?Trabajador $trabajadorSeleccionado = null;

    // Campos de Persona
    public string $prefijo = 'V';

    public string $cedula = '';

    public string $nombre = '';

    public string $apellido = '';

    public string $fecha_nacimiento = '';

    public string $genero = 'F';

    public string $direccion = '';

    public string $email = '';

    public string $codigo_tlf = '0414';

    public string $numero_tlf = '';

    // Campos de Trabajador
    public string $fecha_ingreso = '';

    public string $nivel_academico = 'Universitario';

    public string $estatus_laboral = 'Activo';

    // Asignación de Cargo
    public ?int $cargo_id = null;

    public string $fecha_inicio_cargo = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingEstatusFilter(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->fecha_ingreso = now()->format('Y-m-d');
        $this->fecha_inicio_cargo = now()->format('Y-m-d');
        Flux::modal('crear-trabajador')->show();
    }

    public function save(): void
    {
        $this->validate([
            // Reglas de Persona
            'prefijo' => ['required', 'in:V,E,J,G'],
            'cedula' => ['required', 'string', 'max:15', 'unique:personas,cedula'],
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'fecha_nacimiento' => ['required', 'date'],
            'genero' => ['required', 'in:M,F'],
            'direccion' => ['required', 'string'],
            'email' => ['required', 'email', 'max:100', 'unique:personas,email'],
            'codigo_tlf' => ['required', 'in:0412,0414,0416,0422,0424,0426'],
            'numero_tlf' => ['required', 'digits:7'],

            // Reglas de Trabajador
            'fecha_ingreso' => ['required', 'date'],
            'nivel_academico' => ['required', 'in:Primaria,Bachiller,Universitario'],
            'estatus_laboral' => ['required', 'in:Activo,Inactivo,Reposo'],

            // Reglas de Cargo
            'cargo_id' => ['required', 'exists:cargos,id'],
            'fecha_inicio_cargo' => ['required', 'date'],
        ]);

        $persona = Persona::create([
            'prefijo' => $this->prefijo,
            'cedula' => $this->cedula,
            'nombre' => $this->nombre,
            'apellido' => $this->apellido,
            'fecha_nacimiento' => $this->fecha_nacimiento,
            'genero' => $this->genero,
            'direccion' => $this->direccion,
            'email' => $this->email,
            'codigo_tlf' => $this->codigo_tlf,
            'numero_tlf' => $this->numero_tlf,
        ]);

        $trabajador = $persona->trabajador()->create([
            'fecha_ingreso' => $this->fecha_ingreso,
            'nivel_academico' => $this->nivel_academico,
            'estatus_laboral' => $this->estatus_laboral,
        ]);

        $trabajador->cargos()->attach($this->cargo_id, [
            'fecha_inicio' => $this->fecha_inicio_cargo,
            'fecha_fin' => null,
        ]);

        $this->resetForm();
        Flux::modal('crear-trabajador')->close();
        Flux::toast('Trabajador registrado con éxito.', variant: 'success');
    }

    public function showTrabajador(int $id): void
    {
        $this->trabajadorSeleccionado = Trabajador::with(['persona', 'cargos'])->findOrFail($id);
        Flux::modal('ver-trabajador')->show();
    }

    public function editTrabajador(int $id): void
    {
        $trabajador = Trabajador::with(['persona', 'cargos'])->findOrFail($id);
        $this->trabajadorId = $trabajador->id;

        // Cargar Persona
        $this->prefijo = $trabajador->persona->prefijo;
        $this->cedula = $trabajador->persona->cedula;
        $this->nombre = $trabajador->persona->nombre;
        $this->apellido = $trabajador->persona->apellido;
        $this->fecha_nacimiento = $trabajador->persona->fecha_nacimiento->format('Y-m-d');
        $this->genero = $trabajador->persona->genero;
        $this->direccion = $trabajador->persona->direccion;
        $this->email = $trabajador->persona->email;
        $this->codigo_tlf = $trabajador->persona->codigo_tlf;
        $this->numero_tlf = $trabajador->persona->numero_tlf;

        // Cargar Trabajador
        $this->fecha_ingreso = $trabajador->fecha_ingreso->format('Y-m-d');
        $this->nivel_academico = $trabajador->nivel_academico;
        $this->estatus_laboral = $trabajador->estatus_laboral;

        // Cargar Cargo actual (el que no tiene fecha_fin o el último)
        $cargoActual = $trabajador->cargos->whereNull('pivot.fecha_fin')->first() ?? $trabajador->cargos->last();
        $this->cargo_id = $cargoActual?->id;
        $this->fecha_inicio_cargo = $cargoActual?->pivot->fecha_inicio ? date('Y-m-d', strtotime($cargoActual->pivot->fecha_inicio)) : now()->format('Y-m-d');

        Flux::modal('editar-trabajador')->show();
    }

    public function update(): void
    {
        $trabajador = Trabajador::with(['persona', 'cargos'])->findOrFail($this->trabajadorId);

        $this->validate([
            'prefijo' => ['required', 'in:V,E,J,G'],
            'cedula' => ['required', 'string', 'max:15', Rule::unique('personas', 'cedula')->ignore($trabajador->persona->id)],
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'fecha_nacimiento' => ['required', 'date'],
            'genero' => ['required', 'in:M,F'],
            'direccion' => ['required', 'string'],
            'email' => ['required', 'email', 'max:100', Rule::unique('personas', 'email')->ignore($trabajador->persona->id)],
            'codigo_tlf' => ['required', 'in:0412,0414,0416,0422,0424,0426'],
            'numero_tlf' => ['required', 'digits:7'],

            'fecha_ingreso' => ['required', 'date'],
            'nivel_academico' => ['required', 'in:Primaria,Bachiller,Universitario'],
            'estatus_laboral' => ['required', 'in:Activo,Inactivo,Reposo'],

            'cargo_id' => ['required', 'exists:cargos,id'],
        ]);

        $trabajador->persona->update([
            'prefijo' => $this->prefijo,
            'cedula' => $this->cedula,
            'nombre' => $this->nombre,
            'apellido' => $this->apellido,
            'fecha_nacimiento' => $this->fecha_nacimiento,
            'genero' => $this->genero,
            'direccion' => $this->direccion,
            'email' => $this->email,
            'codigo_tlf' => $this->codigo_tlf,
            'numero_tlf' => $this->numero_tlf,
        ]);

        $trabajador->update([
            'fecha_ingreso' => $this->fecha_ingreso,
            'nivel_academico' => $this->nivel_academico,
            'estatus_laboral' => $this->estatus_laboral,
        ]);

        // Verificar si cambió de cargo
        $cargoActual = $trabajador->cargos->whereNull('pivot.fecha_fin')->first();
        if (! $cargoActual || $cargoActual->id != $this->cargo_id) {
            if ($cargoActual) {
                // Cerrar cargo anterior
                $trabajador->cargos()->updateExistingPivot($cargoActual->id, [
                    'fecha_fin' => now()->format('Y-m-d'),
                ]);
            }
            // Asignar nuevo cargo
            $trabajador->cargos()->attach($this->cargo_id, [
                'fecha_inicio' => now()->format('Y-m-d'),
                'fecha_fin' => null,
            ]);
        }

        $this->resetForm();
        Flux::modal('editar-trabajador')->close();
        Flux::toast('Datos del personal actualizados correctamente.', variant: 'success');
    }

    public function confirmDelete(int $id): void
    {
        $this->trabajadorId = $id;
        $this->trabajadorSeleccionado = Trabajador::with('persona')->findOrFail($id);
        Flux::modal('eliminar-trabajador')->show();
    }

    public function delete(): void
    {
        if ($this->trabajadorId) {
            $trabajador = Trabajador::with('persona')->findOrFail($this->trabajadorId);
            $trabajador->persona->delete();

            $this->resetForm();
            Flux::modal('eliminar-trabajador')->close();
            Flux::toast('Trabajador eliminado del sistema.', variant: 'warning');
        }
    }

    private function resetForm(): void
    {
        $this->reset([
            'trabajadorId', 'trabajadorSeleccionado',
            'prefijo', 'cedula', 'nombre', 'apellido', 'fecha_nacimiento', 'genero',
            'direccion', 'email', 'codigo_tlf', 'numero_tlf',
            'fecha_ingreso', 'nivel_academico', 'estatus_laboral',
            'cargo_id', 'fecha_inicio_cargo',
        ]);
        $this->genero = 'F';
        $this->prefijo = 'V';
        $this->codigo_tlf = '0414';
        $this->nivel_academico = 'Universitario';
        $this->estatus_laboral = 'Activo';
    }

    public function render()
    {
        $trabajadores = Trabajador::with(['persona', 'cargos'])
            ->when($this->search, function ($query) {
                $query->whereHas('persona', function ($q) {
                    $q->where('nombre', 'like', "%{$this->search}%")
                        ->orWhere('apellido', 'like', "%{$this->search}%")
                        ->orWhere('cedula', 'like', "%{$this->search}%");
                })
                    ->orWhereHas('cargos', function ($q) {
                        $q->where('nombre_cargo', 'like', "%{$this->search}%");
                    });
            })
            ->when($this->estatusFilter, function ($query) {
                $query->where('estatus_laboral', $this->estatusFilter);
            })
            ->latest('id')
            ->paginate(8);

        return view('livewire.trabajadores.index', [
            'trabajadores' => $trabajadores,
            'cargosDisponibles' => Cargo::orderBy('nombre_cargo')->get(),
        ]);
    }
}
