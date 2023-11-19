<?php

namespace App\Controller;

use App\Entity\Asesoria;
use App\Entity\Chat;
use App\Entity\Message;
use App\Entity\Usuario;
use App\Service\GeneradorDeMensajes;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use PhpParser\Node\Expr\Empty_;
use stdClass;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mercure\Authorization;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
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
        $chats = $entityManager->getRepository(Chat::class)->findAllByUserWithPagination($page, $usuarioLogueado->getId());

        $limit = 20;

        $total = $chats->count();

        $totalPages = (int) ceil($total/$limit);
        
        $chatsDatas = [];
        foreach ($chats as $chat) {
          $chatData = [
            'nombreAsesoria' => $chat->getIdAsesoria()->getNombre(),
            'idChat' => $chat->getId(),
          ];

          $ultimoMensaje = $chat->getMessages()->last();

          if($ultimoMensaje){
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
          

          $chatsDatas[] = $chatData;
        }

        return $this->json($generadorDeMensajes->generarRespuesta('Peticion procesada con exito.', ['chats' => $chatsDatas, 'totalPages' => $totalPages]));
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
    public function readOne(EntityManagerInterface $entityManager, int $id, GeneradorDeMensajes $generadorDeMensajes, Request $request, Authorization $authorization): JsonResponse
    {
      // se obtiene los datos del chat
      $chat = $entityManager->getRepository(Chat::class)->find($id);

      if($chat !== null){
        $mercureToken = $authorization->createCookie($request, ['*'])->getValue();
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
          ],
          'mercure' => [
            'token' => $mercureToken,
            'topic' => 'https://localhost/chats/'.$chat->getId()
          ],
        ];

        return $this->json($generadorDeMensajes->generarRespuesta('Solicitud procesada con exito.', $chatData));
      } 
      else {
        // No se encuentra el chat con el id enviado
        return $this->json($generadorDeMensajes->generarRespuesta('No se encontro el chat.'), 404); // "No encontrado".
      }
    }

    #[Route('/{id}', name: 'app_chat_add_message', methods: ['PUT'])]
    public function addMessage(EntityManagerInterface $entityManager, int $id, GeneradorDeMensajes $generadorDeMensajes, Request $request, Security $security, HubInterface $hub): JsonResponse
    {
      // se obtiene los datos del chat
      $chat = $entityManager->getRepository(Chat::class)->find($id);

      // se obtiene los datos del usuario mediante el token
      $usuarioLogueado = $security->getUser();

      // se obtiene el nombre del body de la peticion
      $contenido = $request->request->get('contenido');

      if($chat !== null){
        $newMessage = new Message();

        $newMessage->setFechaEnvio(new DateTime('now'));
        $newMessage->setContenido($contenido);
        $newMessage->setUsuario($usuarioLogueado);

        $chat->addMessage($newMessage);

        $entityManager->persist($newMessage);
        $entityManager->flush();

        $newMessageData = [
          'id' => $newMessage->getId(),
          'contenido' => $newMessage->getContenido(),
          'fechaEnvio' => $newMessage->getFechaEnvio(),
          'usuario' => [
            'nombre' => $newMessage->getUsuario()->getNombre(),
            'apellido' => $newMessage->getUsuario()->getApellido()
          ]
        ];

        $update = new Update(
          'https://localhost/chats/'.$chat->getId(),
          json_encode($newMessageData),
        );

        $hub->publish($update);


        return $this->json($generadorDeMensajes->generarRespuesta('Se envio el mensaje con exito.'));
      } 
      else {
        // No se encuentra el chat con el id enviado
        return $this->json($generadorDeMensajes->generarRespuesta('No se encontro el chat.'), 404); // "No encontrado".
      }
    }
}
