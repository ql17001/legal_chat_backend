<?php

namespace App\Controller;

use App\Entity\Asesoria;
use App\Entity\Usuario;
use App\Service\GeneradorDeMensajes;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/chats', name: 'app_chat')]
class ChatController extends AbstractController
{
    #[Route('', name: 'app_chat_read_all')]
    public function readAll(GeneradorDeMensajes $generadorDeMensajes, Security $security, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
      // se obtiene los datos del usuario mediante el token
      $usuarioLogueado = $security->getUser();

      // se obtiene el search param page, si no existe se le da el valor por defecto 1
      $page = $request->get('page', 1);

      if($usuarioLogueado !== null && $usuarioLogueado instanceof Usuario){
        
        // Se obtienen las asesorias del usuario logueado en la pagina $page
        $asesoriasDelusuario = $entityManager->getRepository(Asesoria::class)->findAllByUserWithPagination($page, $usuarioLogueado->getId());

        $chats = [];
        foreach ($asesoriasDelusuario as $asesoria) {
          $chat = $asesoria->getChat();
          $ultimoMensaje = $chat->getMessages()->last();
          $chats[] = [
            'nombreAsesoria' => $asesoria->getNombre(),
            'ultimoMensaje' => [
              'contenido' => $ultimoMensaje->getContenido(),
              'fechaEnvio' => $ultimoMensaje->getFechaEnvio(),
              'usuario' => [
                'nombre' => $ultimoMensaje->getUsuario()->getNombre(),
                'apellido' => $ultimoMensaje->getUsuario()->getApellido()
              ]
            ]
          ];
        }

        return $this->json($generadorDeMensajes->generarRespuesta('Peticion procesada con exito.', $chats));
      } 
      else {
        // Manejo del caso en el que no se cumple la condición
        $errorResponse = [
            'error' => 'No fue posible consultar los chats porque el usuario no fue encontrado o no es válido.',
        ];
        return $this->json($generadorDeMensajes->generarRespuesta($errorResponse), 404); // "No encontrado".
      }
    }
}
