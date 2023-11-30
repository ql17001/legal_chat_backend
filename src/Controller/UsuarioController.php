<?php

namespace App\Controller;

use Symfony\Bundle\SecurityBundle\Security;
use App\Entity\Usuario;
use App\Service\GeneradorDeMensajes;
use App\Service\Validador;
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
    public function create(EntityManagerInterface $entityManager, Request $request, UserPasswordHasherInterface $passwordHasher, GeneradorDeMensajes $generadorDeMensajes, Validador $validador): JsonResponse
    {
        $email = $request->request->get('email');
        $plainPassword = $request->request->get('password');
        $nombre = $request->request->get('nombre');
        $apellido = $request->request->get('apellido');
        $dui = $request->request->get('dui');
        
        $usuarioExiste = $entityManager->getRepository(Usuario::class)->findOneByEmail($email);

        if($usuarioExiste != null){
          return $this->json(
            $generadorDeMensajes->generarRespuesta("Ya existe un usuario con ese email."), 400
          );
        }

        $errores = [];

        if(!$validador->validarEmail($email)){
          $errores[] = 'El email no es valido o excede el limite de 100 caracteres.';
        }

        if(!$validador->validarNombreApellido($nombre) || !$validador->validarNombreApellido($apellido)){
          $errores[] = 'El nombre y apellido debe tener por lo menos un caracter y maximo 100 caracteres.';
        }

        if(!$validador->validarDUI($dui)){
          $errores[] = 'El DUI no debe incluir guion y debe ser un numero de 9 digitos.';
        }

        if(!$validador->validarContrasenia($plainPassword)){
          $errores[] = 'La contraseña debe contener 8 caracteres como minimo.';
        }

        if(count($errores) === 0){
          $usuario = new Usuario();
          $usuario->setEmail($email);
          $usuario->setNombre($nombre);
          $usuario->setApellido($apellido);
          $usuario->setDui($dui);
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
        }else{
          return $this->json(
            $generadorDeMensajes->generarRespuesta(implode(' ', $errores)), 422
          );
        }
    }

   #[Route('/perfil', name: 'app_usuario_real_read', methods: ['GET'])]
    public function readProfile(GeneradorDeMensajes $generadorDeMensajes, Security $security): JsonResponse
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
  public function updatePassword(EntityManagerInterface $entityManager, Request $request, Security $security,UserPasswordHasherInterface $passwordHasher, GeneradorDeMensajes $generadorDeMensajes, Validador $validador, GeneradorDeMensajes $generador): JsonResponse
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

    // Si el campo de la nueva contraseña no es valido responde con un error 422
    if (!$validador->validarContrasenia($plainPassword)){
      return $this->json($generador->generarRespuesta("La contraseña debe contener al menos 8 caracteres."), 422);
    }
    
    //Hashea la contraseña
    $hashedPassword = $passwordHasher->hashPassword($usuario, $plainPassword);

    // Se actualizan los datos a la entidad
    $usuario->setPassword($hashedPassword);

    // Se aplican los cambios de la entidad en la bd
    $entityManager->flush();
    
    return $this->json([[$generadorDeMensajes->generarRespuesta("Se ha actualizado la contraseña.")]]);
  }

    #[Route('/actualizar-informacion', name: 'app_usuario_profile_edit', methods: ['PUT'])]
  public function updateProfile(EntityManagerInterface $entityManager, Request $request, GeneradorDeMensajes $generadorDeMensajes, Security $security, Validador $validador): JsonResponse
  {

    // Obtiene los valores del body de la request
    $nombre = $request->request->get('nombre');
    $apellido = $request->request->get('apellido');
    $email = $request->request->get('email');
    $dui = $request->request->get('dui');

    // obtiene el id del usuario mediante el token
    $usuarioLogueado = $security->getUser();
    if($usuarioLogueado !== null && $usuarioLogueado instanceof Usuario){
      $usuarioLogueadoObj = ['id' => $usuarioLogueado->getId()];
      $usuario = $entityManager->getRepository(Usuario::class)->find($usuarioLogueadoObj['id']);
    }

    // Si no envia uno responde con un error 422
    if ($nombre == null || $apellido == null || $email == null || $dui == null){
      return $this->json(['error'=>'Se debe enviar toda la informacion del usuario.'], 422);
    }

    $errores = [];

    if(!$validador->validarEmail($email)){
      $errores[] = 'El email no es valido o excede el limite de 100 caracteres.';
    }

    if(!$validador->validarNombreApellido($nombre) || !$validador->validarNombreApellido($apellido)){
      $errores[] = 'El nombre y apellido debe tener por lo menos un caracter y maximo 100 caracteres.';
    }

    if(!$validador->validarDUI($dui)){
      $errores[] = 'El DUI no debe incluir guion y debe ser un numero de 9 digitos.';
    }

    if(count($errores) === 0){
      // Se actualizan los datos a la entidad
      $usuario->setNombre($nombre);
      $usuario->setApellido($apellido);
      $usuario->setEmail($email);
      $usuario->setDui($dui);
  
      $data=['id' => $usuario->getId(), 'nombre' => $usuario->getNombre(), 'apellido' => $usuario->getApellido(), 'email' => $usuario->getEmail(), 'dui' => $usuario->getDui()];
  
      // Se aplican los cambios de la entidad en la bd
      $entityManager->flush();
  
      return $this->json([$generadorDeMensajes->generarRespuesta("Se actualizó la información del usuario.", $data)]);
    }else{
      return $this->json(
        $generadorDeMensajes->generarRespuesta(implode(' ', $errores)), 422
      );
    }
  }

  #[Route('/crear', name: 'app_usuario_create', methods: ['POST'])]
  public function createUser(EntityManagerInterface $entityManager, Request $request, UserPasswordHasherInterface $passwordHasher, GeneradorDeMensajes $generadorDeMensajes, Validador $validador): JsonResponse
  {
      $email = $request->request->get('email');
      $nombre = $request->request->get('nombre');
      $apellido = $request->request->get('apellido');
      $plainPassword = $request->request->get('password');
      $dui = $request->request->get('dui');
      $role = json_decode($request->request->get('roles'));

      $usuarioExiste = $entityManager->getRepository(Usuario::class)->findOneByEmail($email);

      if($usuarioExiste != null){
        return $this->json(
          $generadorDeMensajes->generarRespuesta("Ya existe un usuario con ese email."), 400
        );
      }

      $errores = [];

      if(!$validador->validarEmail($email)){
        $errores[] = 'El email no es valido o excede el limite de 100 caracteres.';
      }

      if(!$validador->validarNombreApellido($nombre) || !$validador->validarNombreApellido($apellido)){
        $errores[] = 'El nombre y apellido debe tener por lo menos un caracter y maximo 100 caracteres.';
      }

      if(!$validador->validarDUI($dui)){
        $errores[] = 'El DUI no debe incluir guion y debe ser un numero de 9 digitos.';
      }

      if(!$validador->validarContrasenia($plainPassword)){
        $errores[] = 'La contraseña debe contener 8 caracteres como minimo.';
      }

      if(!$validador->validarRoles($role)){
        $errores[] = 'Los roles deben ser un array de un elemento, y debe ser uno de los siguientes: Administrador, Asesor o Cliente.';
      }

      if(count($errores) === 0){
        $usuario = new Usuario();
        $usuario->setEmail($email);
        $usuario->setNombre($nombre);
        $usuario->setApellido($apellido);
        $usuario->setDui($dui);
        $usuario->setRoles($role);
        $usuario->setActivo(1);
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
      }else{
        return $this->json(
          $generadorDeMensajes->generarRespuesta(implode(' ', $errores)), 422
        );
      }

  }

  #[Route('', name: 'app_usuario_read_all', methods: ['GET'])]
  public function readAll(EntityManagerInterface $entityManager, Request $request, GeneradorDeMensajes $generadorDeMensajes): JsonResponse
  {
    $limit = 20;

    $page = $request->get('page', 1);

    $activo = $request->get('activo', null);

    $usuarios = $entityManager->getRepository(Usuario::class)->findAllWithPagination($page, $limit, $activo);

    $total = $usuarios->count();

    $totalPages = (int) ceil($total/$limit);

    $data = [];
  
    foreach ($usuarios as $usuario) {
        $data[] = [
            'id' => $usuario->getId(),
            'nombre' => $usuario->getNombre(),
            'apellido' => $usuario->getApellido(),
            'email' => $usuario->getEmail(),
            'rol' => $usuario->getRoles()[0],
        ];
    }
    
    return $this->json($generadorDeMensajes->generarRespuesta('Se proceso la solicitud con exito.', ['totalPages' => $totalPages, 'total' => $total, 'usuarios' => $data]) ); 
  }

      #[Route('/{id}', name: 'app_usuario_delete', methods: ['DELETE'])]
  public function delete(EntityManagerInterface $entityManager, int $id, GeneradorDeMensajes $generadorDeMensajes): JsonResponse
  {

    // Buscar usuario que se desea borrar ingresando su id
    $usuario = $entityManager->getRepository(Usuario::class)->find($id);

    // Si no se encuentra al usuario con el id ingresado, el programa devuelve error 404
    if (!$usuario) {
      return $this->json(['error'=>'No fue posible encontrar usuario con el siguiente id: '.$id], 404);
    }

    // En caso de encontrar al usuario se actualiza el atributo activo a falso al usuario seleccionado
    $usuario->setActivo(!$usuario->isActivo());

    $data=['id' => $usuario->getId(), 'nombre' => $usuario->getNombre(), 'apellido' => $usuario->getApellido()];

    // Se aplican los cambios y se actualiza la BD 
    $entityManager->flush();

    return $this->json($generadorDeMensajes->generarRespuesta("Se ha eliminado al usuario correctamente.", $data));
  }

  #[Route('/{id}', name: 'app_usuario_real_read_one', methods: ['GET'])]
    public function read(EntityManagerInterface $entityManager, int $id, GeneradorDeMensajes $generadorDeMensajes): JsonResponse
  {
 // se obtiene los datos del usuario mediante el token
 $usuario = $entityManager->getRepository(Usuario::class)->find($id);;
    if($usuario !== null){
      $usuario = [
        'nombre' => $usuario->getNombre(),
        'apellido' => $usuario->getApellido(),
        'email' => $usuario->getEmail(),
        'dui' => $usuario->getDui(),
        'rol' => $usuario->getRoles()[0]
      ];
      return $this->json($generadorDeMensajes->generarRespuesta('Solicitud procesada con exito.', $usuario));
    } 
    else {
      // Manejo del caso en el que no se cumple la condición
      return $this->json($generadorDeMensajes->generarRespuesta('No se encontro el usuario.'), 404); // "No encontrado".
    }
  }

  #[Route('/{id}', name: 'app_usuario_update', methods: ['PUT'])]
  public function update(EntityManagerInterface $entityManager, int $id, GeneradorDeMensajes $generadorDeMensajes, Request $request, UserPasswordHasherInterface $passwordHasher): JsonResponse
  {
    // Buscar usuario que se desea borrar ingresando su id
    $usuario = $entityManager->getRepository(Usuario::class)->find($id);

    // Si no se encuentra al usuario con el id ingresado, el programa devuelve error 404
    if (!$usuario) {
      return $this->json($generadorDeMensajes->generarRespuesta('No fue posible encontrar usuario con el siguiente id: '.$id), 404);
    }

    $usuario->setEmail($request->request->get('email'));
    $plainPassword = $request->request->get('password');
    $usuario->setNombre($request->request->get('nombre'));
    $usuario->setApellido($request->request->get('apellido'));
    $usuario->setDui($request->request->get('dui'));
    $usuario->setRoles(json_decode($request->request->get('roles')));
    $hashedPassword = $passwordHasher->hashPassword($usuario, $plainPassword);
    $usuario->setPassword($hashedPassword);

    $data=['id' => $usuario->getId(), 'nombre' => $usuario->getNombre(), 'apellido' => $usuario->getApellido()];

    // Se aplican los cambios y se actualiza la BD 
    $entityManager->flush();

    return $this->json($generadorDeMensajes->generarRespuesta("Se ha actualizado el usuario correctamente.", $data));
  }
}

