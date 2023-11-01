<?php

namespace App\Controller;

use Symfony\Bundle\SecurityBundle\Security;
use App\Entity\Usuario;
use App\Service\GeneradorDeMensajes;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route('/usuario', name: 'app_usuario_real')]
class UsuarioController extends AbstractController
{
    #[Route('/registrarme', name: 'app_usuario_real_create', methods: ['POST'])]
    public function create(EntityManagerInterface $entityManager, Request $request, UserPasswordHasherInterface $passwordHasher, GeneradorDeMensajes $generadorDeMensajes): JsonResponse
    {
        $usuario = new Usuario();
        $usuario->setEmail($request->request->get('email'));
        $plainPassword = $request->request->get('password');
        $usuario->setNombre($request->request->get('nombre'));
        $usuario->setApellido($request->request->get('apellido'));
        $usuario->setDui($request->request->get('dui'));
        $usuario->setRoles(["Cliente"]);
        $usuario->setActivo(true);
        $hashedPassword = $passwordHasher->hashPassword($usuario, $plainPassword);
        $usuario->setPassword($hashedPassword);
        // Se avisa a Doctrine que queremos guardar un nuevo registro pero no se ejecutan las consultas
        $entityManager->persist($usuario);
        // Se ejecutan las consultas SQL para guardar el nuevo registro
        $entityManager->flush();
        $data[] = [
            'id' => $usuario->getId(),
            'email' => $usuario->getEmail(),
            'nombre' => $usuario->getNombre(),
            'apellido' => $usuario->getApellido(),
            'dui' => $usuario->getDui()
        ];            
        return $this->json([
            $generadorDeMensajes->generarRespuesta("Se guardó el nuevo usuario.", $data)
        ]);
    }

   #[Route('/perfil', name: 'app_usuario_real_read', methods: ['GET'])]
    public function read(GeneradorDeMensajes $generadorDeMensajes, Security $security): JsonResponse
  {
 // se obtiene los datos del usuario mediante el token
 $usuarioLogueado = $security->getUser();
 if($usuarioLogueado !== null && $usuarioLogueado instanceof Usuario){
   $usuarioLogueadoObj = [
  'nombre' => $usuarioLogueado->getNombre(),
  'apellido' => $usuarioLogueado->getApellido(),
  'email' => $usuarioLogueado->getEmail(),
  'dui' => $usuarioLogueado->getDui(),
  ];
  return $this->json($usuarioLogueadoObj);
    } 
    else {
      // Manejo del caso en el que no se cumple la condición
      $errorResponse = [
          'error' => 'Usuario no encontrado o no válido',
      ];
      return $this->json($errorResponse, 404); // "No encontrado".
    }
  }

  #[Route('/actualizar-contraseña', name: 'app_usuario_real_edit', methods: ['PUT'])]
  public function updatePassword(EntityManagerInterface $entityManager, Request $request, Security $security,UserPasswordHasherInterface $passwordHasher, GeneradorDeMensajes $generadorDeMensajes): JsonResponse
  {
    //Obtiene el id del usuario usando el token JWT
    $usuarioLogueado = $security->getUser();

    if($usuarioLogueado !== null && $usuarioLogueado instanceof Usuario)
    {
      $usuarioLogueadoObj = ['id' => $usuarioLogueado->getId()];
      $usuario = $entityManager->getRepository(Usuario::class)->find($usuarioLogueadoObj['id']);
    }

    // Obtiene el valor de la nueva contraseña desde body de la request
    $plainPassword = $request->request->get('password');

    // Si el campo de la nueva contraseña está vacío responde con un error 422
    if ($plainPassword == null){
      return $this->json(['error'=>'Se debe enviar la nueva contraseña.'], 422);
    }
    
    //Hashea la contraseña
    $hashedPassword = $passwordHasher->hashPassword($usuario, $plainPassword);

    // Se actualizan los datos a la entidad
    $usuario->setPassword($hashedPassword);

    $data=['id' => $usuario->getId(),'password' => $usuario->getPassword()];
    
    return $this->json([[$generadorDeMensajes->generarRespuesta("Se ha actualizado la contraseña.", $data)]]);
  }

    #[Route('/actualizar-informacion', name: 'app_usuario_real_edit', methods: ['PUT'])]
  public function update(EntityManagerInterface $entityManager, Request $request, GeneradorDeMensajes $generadorDeMensajes, Security $security): JsonResponse
  {

    // obtiene el id del usuario mediante el token
    $usuarioLogueado = $security->getUser();
    if($usuarioLogueado !== null && $usuarioLogueado instanceof Usuario){
      $usuarioLogueadoObj = ['id' => $usuarioLogueado->getId()];
      $usuario = $entityManager->getRepository(Usuario::class)->find($usuarioLogueadoObj['id']);
    }
    
    // Obtiene los valores del body de la request
    $nombre = $request->request->get('nombre');
    $apellido = $request->request->get('apellido');
    $email = $request->request->get('email');
    $dui = $request->request->get('dui');

    // Si no envia uno responde con un error 422
    if ($nombre == null || $apellido == null || $email == null || $dui == null){
      return $this->json(['error'=>'Se debe enviar toda la informacion del usuario.'], 422);
    }

    // Se actualizan los datos a la entidad
    $usuario->setNombre($nombre);
    $usuario->setApellido($apellido);
    $usuario->setEmail($email);
    $usuario->setDui($dui);

    $data=['id' => $usuario->getId(), 'nombre' => $usuario->getNombre(), 'apellido' => $usuario->getApellido(), 'email' => $usuario->getEmail(), 'dui' => $usuario->getDui()];

    // Se aplican los cambios de la entidad en la bd
    $entityManager->flush();

    return $this->json([$generadorDeMensajes->generarRespuesta("Se actualizó la información del usuario.", $data)]);
  }

  #[Route('/crear', name: 'app_usuario_create', methods: ['POST'])]
  public function createUser(EntityManagerInterface $entityManager, Request $request, UserPasswordHasherInterface $passwordHasher, GeneradorDeMensajes $generadorDeMensajes): JsonResponse
  {
      $usuario = new Usuario();
      $usuario->setEmail($request->request->get('email'));
      $plainPassword = $request->request->get('password');
      $usuario->setNombre($request->request->get('nombre'));
      $usuario->setApellido($request->request->get('apellido'));
      $usuario->setDui($request->request->get('dui'));
      $usuario->setRoles($request->request->get('roles'));
      $usuario->setActivo($request->request->get('activo'));
      $hashedPassword = $passwordHasher->hashPassword($usuario, $plainPassword);
      $usuario->setPassword($hashedPassword);
      // Se avisa a Doctrine que queremos guardar un nuevo registro pero no se ejecutan las consultas
      $entityManager->persist($usuario);
      // Se ejecutan las consultas SQL para guardar el nuevo registro
      $entityManager->flush();
      $data[] = [
          'id' => $usuario->getId(),
          'email' => $usuario->getEmail(),
          'nombre' => $usuario->getNombre(),
          'apellido' => $usuario->getApellido(),
          'dui' => $usuario->getDui(),
          'activo'=> $usuario->isActivo(),
          'rol'=> $usuario->getRoles()
      ];            
      return $this->json([
          $generadorDeMensajes->generarRespuesta("Se guardó el nuevo usuario.", $data)
      ]);
  }
}
