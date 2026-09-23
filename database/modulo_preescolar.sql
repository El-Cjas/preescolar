CREATE DATABASE modulo_preescolar;
USE modulo_preescolar;

CREATE TABLE persona(
    id_persona INT PRIMARY KEY AUTO_INCREMENT,

    prefijo_cedula ENUM('V', 'E', 'J', 'G') NOT NULL,
    cedula VARCHAR(15)NOT NULL UNIQUE,

    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    genero ENUM('M', 'F') NOT NULL,
    direccion TEXT NOT NULL,
    correo_electronico VARCHAR(100) NOT NULL UNIQUE,
    codigo_tlf ENUM('0412', '0414', '0416', '0422', '0424', '0426') NOT NULL,
    numero_tlf VARCHAR(7) NOT NULL,

    CONSTRAINT chk_numero CHECK (numero_tlf REGEXP '^[0-9]{7}$'),

    UNIQUE (prefijo_cedula, cedula)
);

CREATE TABLE alumno(
    id_persona INT PRIMARY KEY,
    fecha_ingreso DATE NOT NULL,
    lugar_nacimiento VARCHAR(100) NOT NULL,
    talla_camisa VARCHAR(5),
    talla_pantalon VARCHAR(5),
    peso DECIMAL(5,2),
    estatura DECIMAL(5,2),
    tipo_sangre ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-') NOT NULL,

    CONSTRAINT fk_alumno_persona 
    FOREIGN KEY (id_persona) 
    REFERENCES persona(id_persona) ON DELETE CASCADE
);

CREATE TABLE representante(
    id_persona INT PRIMARY KEY,
    ocupacion VARCHAR(100) NOT NULL,

    CONSTRAINT fk_representante_persona 
    FOREIGN KEY (id_persona) 
    REFERENCES persona(id_persona) ON DELETE CASCADE
);

CREATE TABLE alumno_representante(
    id_registro INT PRIMARY KEY AUTO_INCREMENT,

    id_alumno INT,
    id_representante INT,

    parentesco VARCHAR(50) NOT NULL,
    representante_legal BOOLEAN NOT NULL DEFAULT 0,

    CONSTRAINT fk_alumno_representante_alumno 
    FOREIGN KEY (id_alumno) 
    REFERENCES alumno(id_persona) ON DELETE CASCADE,

    CONSTRAINT fk_alumno_representante_representante 
    FOREIGN KEY (id_representante) 
    REFERENCES representante(id_persona) ON DELETE CASCADE
);

CREATE TABLE trabajador(
    id_persona INT PRIMARY KEY,
    fecha_ingreso DATE NOT NULL,
    nivel_academico ENUM('Primaria', 'Bachiller', 'Universitario') NOT NULL,
    estatus_laboral ENUM('Activo', 'Inactivo', 'Reposo') NOT NULL,

    CONSTRAINT fk_trabajador_persona
    FOREIGN KEY (id_persona) 
    REFERENCES persona(id_persona) ON DELETE CASCADE
);

CREATE TABLE cargo(
    id_cargo INT PRIMARY KEY AUTO_INCREMENT,
    nombre_cargo VARCHAR(100) NOT NULL,
    descripcion TEXT
);

CREATE TABLE trabajador_cargo(
    id_asignacion INT PRIMARY KEY AUTO_INCREMENT,

    id_trabajador INT,
    id_cargo INT,

    fecha_inicio DATE NOT NULL,
    fecha_fin DATE,

    CONSTRAINT fk_trabajador_cargo_trabajador 
    FOREIGN KEY (id_trabajador) 
    REFERENCES trabajador(id_persona) ON DELETE CASCADE,

    CONSTRAINT fk_trabajador_cargo_cargo 
    FOREIGN KEY (id_cargo) 
    REFERENCES cargo(id_cargo) ON DELETE CASCADE
);
