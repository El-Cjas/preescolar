<?php

namespace App\Livewire\Representantes;

use App\Models\Persona;
use App\Models\Representante;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Representantes')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $representanteId = null;

    public ?Representante $representanteSeleccionado = null;

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

    // Campo de Representante
    public string $ocupacion = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        Flux::modal('crear-representante')->show();
    }

    public function save(): void
    {
        $this->validate([
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
            'ocupacion' => ['required', 'string', 'max:100'],
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

        $persona->representante()->create([
            'ocupacion' => $this->ocupacion,
        ]);

        $this->resetForm();
        Flux::modal('crear-representante')->close();
        Flux::toast('Representante registrado con éxito.', variant: 'success');
    }

    public function showRepresentante(int $id): void
    {
        $this->representanteSeleccionado = Representante::with(['persona', 'alumnos.persona'])->findOrFail($id);
        Flux::modal('ver-representante')->show();
    }

    public function editRepresentante(int $id): void
    {
        $rep = Representante::with('persona')->findOrFail($id);
        $this->representanteId = $rep->id;

        $this->prefijo = $rep->persona->prefijo;
        $this->cedula = $rep->persona->cedula;
        $this->nombre = $rep->persona->nombre;
        $this->apellido = $rep->persona->apellido;
        $this->fecha_nacimiento = $rep->persona->fecha_nacimiento->format('Y-m-d');
        $this->genero = $rep->persona->genero;
        $this->direccion = $rep->persona->direccion;
        $this->email = $rep->persona->email;
        $this->codigo_tlf = $rep->persona->codigo_tlf;
        $this->numero_tlf = $rep->persona->numero_tlf;
        $this->ocupacion = $rep->ocupacion;

        Flux::modal('editar-representante')->show();
    }

    public function update(): void
    {
        $rep = Representante::with('persona')->findOrFail($this->representanteId);

        $this->validate([
            'prefijo' => ['required', 'in:V,E,J,G'],
            'cedula' => ['required', 'string', 'max:15', Rule::unique('personas', 'cedula')->ignore($rep->persona->id)],
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'fecha_nacimiento' => ['required', 'date'],
            'genero' => ['required', 'in:M,F'],
            'direccion' => ['required', 'string'],
            'email' => ['required', 'email', 'max:100', Rule::unique('personas', 'email')->ignore($rep->persona->id)],
            'codigo_tlf' => ['required', 'in:0412,0414,0416,0422,0424,0426'],
            'numero_tlf' => ['required', 'digits:7'],
            'ocupacion' => ['required', 'string', 'max:100'],
        ]);

        $rep->persona->update([
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

        $rep->update([
            'ocupacion' => $this->ocupacion,
        ]);

        $this->resetForm();
        Flux::modal('editar-representante')->close();
        Flux::toast('Datos del representante actualizados.', variant: 'success');
    }

    public function confirmDelete(int $id): void
    {
        $this->representanteId = $id;
        $this->representanteSeleccionado = Representante::with(['persona', 'alumnos'])->findOrFail($id);
        Flux::modal('eliminar-representante')->show();
    }

    public function delete(): void
    {
        if ($this->representanteId) {
            $rep = Representante::with('persona')->findOrFail($this->representanteId);
            $rep->persona->delete();

            $this->resetForm();
            Flux::modal('eliminar-representante')->close();
            Flux::toast('Representante eliminado.', variant: 'warning');
        }
    }

    private function resetForm(): void
    {
        $this->reset([
            'representanteId', 'representanteSeleccionado',
            'prefijo', 'cedula', 'nombre', 'apellido', 'fecha_nacimiento', 'genero',
            'direccion', 'email', 'codigo_tlf', 'numero_tlf', 'ocupacion',
        ]);
        $this->genero = 'F';
        $this->prefijo = 'V';
        $this->codigo_tlf = '0414';
    }

    public function render()
    {
        $representantes = Representante::with(['persona', 'alumnos.persona'])
            ->when($this->search, function ($query) {
                $query->where('ocupacion', 'like', "%{$this->search}%")
                    ->orWhereHas('persona', function ($q) {
                        $q->where('nombre', 'like', "%{$this->search}%")
                            ->orWhere('apellido', 'like', "%{$this->search}%")
                            ->orWhere('cedula', 'like', "%{$this->search}%");
                    });
            })
            ->latest('id')
            ->paginate(8);

        return view('livewire.representantes.index', [
            'representantes' => $representantes,
        ]);
    }
}
