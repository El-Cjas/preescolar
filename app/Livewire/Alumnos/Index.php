<?php

namespace App\Livewire\Alumnos;

use App\Models\Alumno;
use App\Models\Persona;
use App\Models\Representante;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Alumnos')]
class Index extends Component
{
    use WithPagination;

    // Buscador
    public string $search = '';

    // Alumno seleccionado para Ver, Editar o Eliminar
    public ?int $alumnoId = null;

    public ?Alumno $alumnoSeleccionado = null;

    // Cédula Escolar
    public bool $es_cedula_escolar = true;

    public int $parto = 1;

    // Campos de Persona
    public string $prefijo = 'V';

    public string $cedula = '';

    public string $nombre = '';

    public string $apellido = '';

    public string $fecha_nacimiento = '';

    public string $genero = 'M';

    public string $direccion = '';

    public string $email = '';

    public string $codigo_tlf = '0414';

    public string $numero_tlf = '';

    // Campos de Alumno
    public string $fecha_ingreso = '';

    public string $lugar_nacimiento = '';

    public string $tipo_sangre = 'O+';

    public ?string $talla_camisa = null;

    public ?string $talla_pantalon = null;

    public ?string $peso = null;

    public ?string $estatura = null;

    // Representante
    public ?string $representante_id = '';

    public string $parentesco = 'Madre';

    public function mount(): void
    {
        $this->fecha_ingreso = now()->format('Y-m-d');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updated($property): void
    {
        if (in_array($property, ['fecha_nacimiento', 'representante_id', 'parto', 'es_cedula_escolar']) && ! $this->alumnoId) {
            $this->generarCedulaEscolar();
        }
    }

    // 🇻🇪 CÁLCULO DE CÉDULA ESCOLAR (11 dígitos)
    public function generarCedulaEscolar(): void
    {
        if (! $this->es_cedula_escolar) {
            return;
        }

        if (empty($this->fecha_nacimiento) || empty($this->representante_id)) {
            return;
        }

        $rep = Representante::with('persona')->find($this->representante_id);
        if (! $rep) {
            return;
        }

        $digitoParto = (string) max(1, $this->parto);
        $anio = date('y', strtotime($this->fecha_nacimiento));
        $cedulaLimpia = preg_replace('/[^0-9]/', '', $rep->persona->cedula);
        $cedula8Digitos = str_pad($cedulaLimpia, 8, '0', STR_PAD_LEFT);

        $this->cedula = $digitoParto.$anio.$cedula8Digitos;
    }

    // 1. VER FICHA DEL ALUMNO
    public function showAlumno(int $id): void
    {
        $this->alumnoSeleccionado = Alumno::with(['persona', 'representantes.persona'])->findOrFail($id);
        Flux::modal('ver-alumno')->show();
    }

    // 2. ABRIR MODAL PARA CREAR
    public function openCreateModal(): void
    {
        $this->resetForm();
        Flux::modal('crear-alumno')->show();
    }

    // 3. GUARDAR NUEVO ALUMNO
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
            'fecha_ingreso' => ['required', 'date'],
            'lugar_nacimiento' => ['required', 'string', 'max:100'],
            'tipo_sangre' => ['required', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'],
            'talla_camisa' => ['nullable', 'string', 'max:5'],
            'talla_pantalon' => ['nullable', 'string', 'max:5'],
            'peso' => ['nullable', 'numeric'],
            'estatura' => ['nullable', 'numeric'],
            'representante_id' => ['nullable', 'exists:representantes,id'],
            'parentesco' => ['nullable', 'string', 'max:50'],
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

        $alumno = $persona->alumno()->create([
            'fecha_ingreso' => $this->fecha_ingreso,
            'lugar_nacimiento' => $this->lugar_nacimiento,
            'tipo_sangre' => $this->tipo_sangre,
            'talla_camisa' => $this->talla_camisa ?: null,
            'talla_pantalon' => $this->talla_pantalon ?: null,
            'peso' => $this->peso ?: null,
            'estatura' => $this->estatura ?: null,
        ]);

        if (! empty($this->representante_id)) {
            $alumno->representantes()->attach($this->representante_id, [
                'parentesco' => $this->parentesco ?: 'Representante',
                'representante_legal' => true,
            ]);
        }

        $this->resetForm();
        Flux::modal('crear-alumno')->close();
        Flux::toast('¡Alumno inscrito con éxito!', variant: 'success');
    }

    // 4. CARGAR DATOS PARA EDITAR
    public function editAlumno(int $id): void
    {
        $alumno = Alumno::with(['persona', 'representantes'])->findOrFail($id);
        $this->alumnoId = $alumno->id;

        $this->prefijo = $alumno->persona->prefijo;
        $this->cedula = $alumno->persona->cedula;
        $this->nombre = $alumno->persona->nombre;
        $this->apellido = $alumno->persona->apellido;
        $this->fecha_nacimiento = $alumno->persona->fecha_nacimiento->format('Y-m-d');
        $this->genero = $alumno->persona->genero;
        $this->direccion = $alumno->persona->direccion;
        $this->email = $alumno->persona->email;
        $this->codigo_tlf = $alumno->persona->codigo_tlf;
        $this->numero_tlf = $alumno->persona->numero_tlf;

        $this->fecha_ingreso = $alumno->fecha_ingreso->format('Y-m-d');
        $this->lugar_nacimiento = $alumno->lugar_nacimiento;
        $this->tipo_sangre = $alumno->tipo_sangre;
        $this->talla_camisa = $alumno->talla_camisa;
        $this->talla_pantalon = $alumno->talla_pantalon;
        $this->peso = $alumno->peso ? (string) $alumno->peso : null;
        $this->estatura = $alumno->estatura ? (string) $alumno->estatura : null;

        $rep = $alumno->representantes->first();
        $this->representante_id = $rep ? (string) $rep->id : '';
        $this->parentesco = $rep ? $rep->pivot->parentesco : 'Madre';

        Flux::modal('editar-alumno')->show();
    }

    // 5. ACTUALIZAR DATOS
    public function update(): void
    {
        $alumno = Alumno::with('persona')->findOrFail($this->alumnoId);

        $this->validate([
            'prefijo' => ['required', 'in:V,E,J,G'],
            'cedula' => ['required', 'string', 'max:15', Rule::unique('personas', 'cedula')->ignore($alumno->persona->id)],
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'fecha_nacimiento' => ['required', 'date'],
            'genero' => ['required', 'in:M,F'],
            'direccion' => ['required', 'string'],
            'email' => ['required', 'email', 'max:100', Rule::unique('personas', 'email')->ignore($alumno->persona->id)],
            'codigo_tlf' => ['required', 'in:0412,0414,0416,0422,0424,0426'],
            'numero_tlf' => ['required', 'digits:7'],
            'fecha_ingreso' => ['required', 'date'],
            'lugar_nacimiento' => ['required', 'string', 'max:100'],
            'tipo_sangre' => ['required', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'],
            'talla_camisa' => ['nullable', 'string', 'max:5'],
            'talla_pantalon' => ['nullable', 'string', 'max:5'],
            'peso' => ['nullable', 'numeric'],
            'estatura' => ['nullable', 'numeric'],
            'representante_id' => ['nullable', 'exists:representantes,id'],
            'parentesco' => ['nullable', 'string', 'max:50'],
        ]);

        $alumno->persona->update([
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

        $alumno->update([
            'fecha_ingreso' => $this->fecha_ingreso,
            'lugar_nacimiento' => $this->lugar_nacimiento,
            'tipo_sangre' => $this->tipo_sangre,
            'talla_camisa' => $this->talla_camisa ?: null,
            'talla_pantalon' => $this->talla_pantalon ?: null,
            'peso' => $this->peso ?: null,
            'estatura' => $this->estatura ?: null,
        ]);

        if (! empty($this->representante_id)) {
            $alumno->representantes()->sync([
                $this->representante_id => [
                    'parentesco' => $this->parentesco ?: 'Representante',
                    'representante_legal' => true,
                ],
            ]);
        }

        $this->resetForm();
        Flux::modal('editar-alumno')->close();
        Flux::toast('Datos del alumno actualizados con éxito.', variant: 'success');
    }

    // 6. CONFIRMAR ELIMINACIÓN
    public function confirmDelete(int $id): void
    {
        $this->alumnoId = $id;
        $this->alumnoSeleccionado = Alumno::with('persona')->findOrFail($id);
        Flux::modal('eliminar-alumno')->show();
    }

    // 7. ELIMINAR ALUMNO
    public function delete(): void
    {
        if ($this->alumnoId) {
            $alumno = Alumno::with('persona')->findOrFail($this->alumnoId);
            // Al borrar la persona, la base de datos borra al alumno y relaciones por CASCADE
            $alumno->persona->delete();

            $this->resetForm();
            Flux::modal('eliminar-alumno')->close();
            Flux::toast('Alumno retirado del sistema.', variant: 'warning');
        }
    }

    private function resetForm(): void
    {
        $this->reset([
            'alumnoId', 'alumnoSeleccionado',
            'prefijo', 'cedula', 'nombre', 'apellido', 'fecha_nacimiento', 'genero',
            'direccion', 'email', 'codigo_tlf', 'numero_tlf', 'fecha_ingreso',
            'lugar_nacimiento', 'tipo_sangre', 'talla_camisa', 'talla_pantalon',
            'peso', 'estatura', 'representante_id', 'parentesco', 'parto',
        ]);
        $this->fecha_ingreso = now()->format('Y-m-d');
        $this->parto = 1;
    }

    public function render()
    {
        $alumnos = Alumno::with(['persona', 'representantes.persona'])
            ->when($this->search, function ($query) {
                $query->whereHas('persona', function ($q) {
                    $q->where('nombre', 'like', "%{$this->search}%")
                        ->orWhere('apellido', 'like', "%{$this->search}%")
                        ->orWhere('cedula', 'like', "%{$this->search}%");
                });
            })
            ->latest('id')
            ->paginate(7);

        $representantes = Representante::with('persona')->get();

        return view('livewire.alumnos.index', [
            'alumnos' => $alumnos,
            'representantes' => $representantes,
        ]);
    }
}
