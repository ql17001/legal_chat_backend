<?php
  namespace App\Service;

  class Validador {
    // Verificar si la longitud está entre 1 y 100 caracteres
    public function validarNombreApellido($nombreOApellido) {
      $longitud = strlen($nombreOApellido);
      if ($longitud >= 1 && $longitud <= 100) {
        return true; 
      } else {
        return false; 
      }
    }

    // Verificar si el DUI es un número entero de exactamente 9 caracteres
    public function validarDUI($dui) {
      if (is_numeric($dui) && strlen($dui) === 9 && strpos($dui, '.') === false && strpos($dui, ',') === false) {
        return true;
      } else {
        return false; 
      }
    }

    //Verificar si la contraseña tiene al menos 8 caracteres de longitud
    public function validarContrasenia($contrasenia) {
      if (strlen($contrasenia) >= 8) {
        return true; 
      } else {
        return false; 
      }
    }

    // Verificar si el array de roles tiene solo un elemento
    public function validarRoles($roles) {        
      if (is_array($roles) && count($roles) === 1) {
        $rolValido = ["Administrador", "Asesor", "Cliente"];
          if (in_array($roles[0], $rolValido)) {
          return true; 
        }
      }
      return false; 
    }
  }