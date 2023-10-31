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

    #[Route('/actualizar-contraseña', name: 'app_usuario_real_edit', methods: ['PUT'])]
  public function update(EntityManagerInterface $entityManager, Request $request, Security $security, GeneradorDeMensajes $generadorDeMensajes): JsonResponse
  {
    //Obtiene el id del usuario usando el token JWT
    $usuarioLogueado = $security->getUser();

    if($usuarioLogueado !== null && $usuarioLogueado instanceof Usuario)
    {
      $usuarioLogueadoObj = ['id' => $usuarioLogueado->getId()];
      $usuario = $entityManager->getRepository(Usuario::class)->find($usuarioLogueadoObj['id']);
    }

    // Obtiene el valor de la nueva contraseña desde body de la request
    $password = $request->request->get('password');

    // Si el campo de la nueva contraseña está vacío responde con un error 422
    if ($password == null){
      return $this->json(['error'=>'Se debe enviar la nueva contraseña.'], 422);
    }

    // Se actualizan los datos a la entidad
    $usuario->setPassword($password);

    $data=['id' => $usuario->getId(),'password' => $usuario->getPassword()];

    // Se aplican los cambios de la entidad en la bd
    $entityManager->flush();

    return $this->json([[$generadorDeMensajes->generarRespuesta("Se ha actualizado la contraseña.", $data)]]);
   
  }
}
