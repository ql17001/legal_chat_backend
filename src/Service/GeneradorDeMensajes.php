<?php
namespace App\Service;
class GeneradorDeMensajes {

    public function generarRespuesta($message, $data) {
      $respuesta = ['message' => $message, 'data' => $data];
      return $respuesta;
  }
  
  }