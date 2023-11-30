<?php
namespace App\Service;
class GeneradorDeMensajes {

    public function generarRespuesta($message, $data = null) {
      $respuesta = ['message' => $message];
      if($data !== null){
        $respuesta['data'] = $data;
      }
      return $respuesta;
  }
  
  }