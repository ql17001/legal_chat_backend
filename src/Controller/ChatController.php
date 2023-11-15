<?php

namespace App\Controller;

use App\Entity\Asesoria;
use App\Entity\Chat;
use App\Entity\Usuario;
use App\Service\GeneradorDeMensajes;
use Doctrine\ORM\EntityManagerInterface;
use PhpParser\Node\Expr\Empty_;
use stdClass;
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
          $chatData = [
            'nombreAsesoria' => $asesoria->getNombre(),
          ];

          if($chat !== null){
            $chatData = [
              'idChat' => $chat->getId(),
              'nombreAsesoria' => $asesoria->getNombre(),
            ];
            $ultimoMensaje = $chat->getMessages()->last();

            if($ultimoMensaje !== null){
              $ultimoMensajeData = [
                'contenido' => $ultimoMensaje->getContenido(),
                'fechaEnvio' => $ultimoMensaje->getFechaEnvio(),
                'usuario' => [
                  'nombre' => $ultimoMensaje->getUsuario()->getNombre(),
                  'apellido' => $ultimoMensaje->getUsuario()->getApellido()
                ]
              ];
              
              $chatData['ultimoMensaje'] = $ultimoMensajeData;
            }
          }

          $chats[] = $chatData;
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

    #[Route('/{id}', name: 'app_chat_read_one', methods: ['GET'])]
    public function readOne(EntityManagerInterface $entityManager, int $id, GeneradorDeMensajes $generadorDeMensajes): JsonResponse
    {
      // se obtiene los datos del chat
      $chat = $entityManager->getRepository(Chat::class)->find($id);

      if($chat !== null){
        // se obtiene los datos de la asesoria del chat
        $asesoria = $entityManager->getRepository(Asesoria::class)->find($chat->getIdAsesoria());

        $messages = [];

        foreach($chat->getMessages() as $message) {
          $messages[] = [
            "fechaEnvio" => $message->getFechaEnvio(),
            "contenido" => $message->getContenido(),
            "usuario" => [
              "nombre" => $message->getUsuario()->getNombre(),
              "apellido" => $message->getUsuario()->getApellido()
            ]
          ];
        }

        $chatData = [
          'asesoria' => [
            "nombre" => $asesoria->getNombre(),
            "estado" => $asesoria->getEstado(),
            "fecha" => $asesoria->getFecha(),
            "cliente" => [
              "nombre" =>  $asesoria->getIdCliente()->getNombre(),
              "apellido" => $asesoria->getIdCliente()->getApellido()
            ]
          ],
          'chat' => [
            "id" => $chat->getId(),
            "fechaCreacion" => $chat->getFechaCreacion(),
            "mensajes" => $messages
          ]
        ];

        return $this->json($generadorDeMensajes->generarRespuesta('Solicitud procesada con exito.', $chatData));
      } 
      else {
        // No se encuentra el chat con el id enviado
        return $this->json($generadorDeMensajes->generarRespuesta('No se encontro el chat.'), 404); // "No encontrado".
      }
    }
}
