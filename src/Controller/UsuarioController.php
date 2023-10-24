<?php

namespace App\Controller;

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
}
