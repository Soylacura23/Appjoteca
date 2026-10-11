<?php
class Validator
{
    /** @var string[] */
    private array $errores = [];

    public function requerido(string $valor, string $campo): self
    {
        if (trim($valor) === '') {
            $this->errores[] = "El campo $campo es obligatorio.";
        }
        return $this;
    }

    public function longitud(string $valor, int $min, int $max, string $campo): self
    {
        $len = mb_strlen(trim($valor));
        if ($len < $min) {
            $this->errores[] = "El campo $campo debe tener al menos $min caracteres.";
        }
        if ($len > $max) {
            $this->errores[] = "El campo $campo no puede exceder $max caracteres.";
        }
        return $this;
    }

    public function regex(string $valor, string $patron, string $mensaje): self
    {
        if (!preg_match($patron, $valor)) {
            $this->errores[] = $mensaje;
        }
        return $this;
    }

    public function email(string $valor): self
    {
        if (!filter_var($valor, FILTER_VALIDATE_EMAIL)) {
            $this->errores[] = 'El correo electrónico no es válido.';
        }
        return $this;
    }

    /**
     * @param string[] $dominios  Lista de fragmentos permitidos (ej. ['.edu', '.ac.'])
     */
    public function emailInstitucional(string $valor, array $dominios): self
    {
        $lower = mb_strtolower($valor);
        foreach ($dominios as $d) {
            if (str_contains($lower, $d)) {
                return $this;
            }
        }
        $this->errores[] = 'Debes usar un correo institucional (.edu, .edu.co, .ac, .gob, etc.).';
        return $this;
    }

    public function password(string $valor, int $min = 8, int $max = 72): self
    {
        $len = strlen($valor);
        if ($len < $min) {
            $this->errores[] = "La contraseña debe tener al menos $min caracteres.";
        }
        if ($len > $max) {
            
            $this->errores[] = "La contraseña no puede exceder $max caracteres.";
        }
        if (!preg_match('/[A-Z]/', $valor)) {
            $this->errores[] = 'La contraseña debe incluir al menos una letra mayúscula.';
        }
        if (!preg_match('/[a-z]/', $valor)) {
            $this->errores[] = 'La contraseña debe incluir al menos una letra minúscula.';
        }
        if (!preg_match('/[0-9]/', $valor)) {
            $this->errores[] = 'La contraseña debe incluir al menos un número.';
        }
        return $this;
    }

    public function iguales(string $a, string $b, string $mensaje): self
    {
        if ($a !== $b) {
            $this->errores[] = $mensaje;
        }
        return $this;
    }

    public function falla(): bool
    {
        return !empty($this->errores);
    }

    /** @return string[] */
    public function errores(): array
    {
        return $this->errores;
    }

    public function primero(): string
    {
        return $this->errores[0] ?? '';
    }
}