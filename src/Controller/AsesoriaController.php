<?php

namespace App\Controller;

use App\Entity\Asesoria;
use App\Entity\Usuario;
use DateTime;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Service\GeneradorDeMensajes;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

#[Route('/asesorias', name: 'app_asesoria')]
class AsesoriaController extends AbstractController
{
    #[Route('', name: 'app_read_all_asesorias', methods: ['GET'])]
    public function read(EntityManagerInterface $entityManager, Request $request, GeneradorDeMensajes $generadorDeMensajes): JsonResponse
    {
        $limit = 20;

        $page = $request->get('page', 1);

        $filtro = $request->get('filtro', null);
        if ($filtro === 'ALL') {
            $filtro = null;
        }
        $asesorias = $entityManager->getRepository(Asesoria::class)->findAllWithPagination($page, $limit, $filtro);

        $total = $asesorias->count();

        $lastPage = (int) ceil($total / $limit);

        $data = [];

        foreach ($asesorias as $asesoria) {

            $usuarioid = ['id' => $asesoria->getIdCliente()];
            $usuario = $entityManager->getRepository(Usuario::class)->find($usuarioid['id']);
            $usuario_array = [
                'nombre' => $usuario->getNombre(),
                'apellido' => $usuario->getApellido(),
            ];

            $asesor = $asesoria->getIdAsesor();

            if ($asesor == null) {
                $data[] = [
                    'id' => $asesoria->getId(),
                    'nombre' => $asesoria->getNombre(),
                    'estado' => $asesoria->getEstado(),
                    'fecha' => $asesoria->getFecha(),
                    'cliente' => $usuario_array
                ];
            } else {
                $asesor = $entityManager->getRepository(Usuario::class)->find($asesor);
                $asesor_array = ['nombre' => $asesor->getNombre(), 'apellido' => $asesor->getApellido()];
                $data[] = [
                    'id' => $asesoria->getId(),
                    'nombreAsesoria' => $asesoria->getNombre(),
                    'estado' => $asesoria->getEstado(),
                    'fecha' => $asesoria->getFecha(),
                    'cliente' => $usuario_array,
                    'asesor' => $asesor_array
                ];
            }
        }
        $mensajeFiltro = $this->getMensajeFiltro($filtro);
        return $this->json([$generadorDeMensajes->generarRespuesta($mensajeFiltro, $data),
            'total' => $total,
            'lastPage' => $lastPage,
            'page' => $page,
        ]);
    }

    private function getMensajeFiltro($filtro): string
    {
        switch ($filtro) {
            case 's':
                return "Asesorías sin asesor:";
            case 't':
                return "Asesorías terminadas:";
            case 'e':
                return "Asesorías en proceso:";
            default:
                return "Todas las asesorías:";
        }
    }

    #[Route('/solicitar', name: 'app_asesoria_create', methods: ['POST'])]
    public function create(EntityManagerInterface $entityManager, Request $request, Security $security, GeneradorDeMensajes $generadorDeMensajes): JsonResponse
    {
        $asesoria = new Asesoria();
        $asesoria->setNombre($request->request->get('nombre'));
        $asesoria->setEstado('s');
        $asesoria->setFecha(new DateTime('now'));
        $usuario = $security->getUser();
        if ($usuario !== null && $usuario instanceof Usuario) {
            $usuarioid = ['id' => $usuario->getId()];
            $usuario = $entityManager->getRepository(Usuario::class)->find($usuarioid['id']);
            $usuario_array = [
                'nombre' => $usuario->getNombre(),
                'apellido' => $usuario->getApellido(),
                'email' => $usuario->getEmail(),
                'dui' => $usuario->getDui()
            ];
        } else {
            $errorResponse = ['error' => 'Usuario no encontrado o no válido',];
            return $this->json($errorResponse, 404); // "No encontrado".
        }
        $asesoria->setidCliente($usuario);
        $asesoria->setIdAsesor(null);
        // Se avisa a Doctrine que queremos guardar un nuevo registro pero no se ejecutan las consultas
        // Se ejecutan las consultas SQL para guardar el nuevo registro
        $entityManager->persist($asesoria);
        $entityManager->flush();
        $data[] = [
            'id' => $asesoria->getId(),
            'nombre' => $asesoria->getNombre(),
            'estado' => $asesoria->getEstado(),
            'fecha' => $asesoria->getFecha()->format('d/m/Y, H:i:s'),
            'asesor' => $asesoria->getIdAsesor(),
            'usuario' => $usuario_array
        ];
        return $this->json([
            $generadorDeMensajes->generarRespuesta("Se ha solicitado la asesoría.", $data)
        ]);
    }

    #[Route('/sin-asesor', name: 'app_read_all_asesorias_sin_asesor', methods: ['GET'])]
    public function readAll(EntityManagerInterface $entityManager, Request $request, GeneradorDeMensajes $generadorDeMensajes): JsonResponse
    {
        $repositorio = $entityManager->getRepository(Asesoria::class);

        $limit = 20;

        $page = $request->get('page', 1);

        $asesorias = $repositorio->findAllWithPagination($page, $limit);

        $total = $asesorias->count();

        $lastPage = (int) ceil($total / $limit);

        $data = [];

        foreach ($asesorias as $asesoria) {

            $usuarioid = ['id' => $asesoria->getIdCliente()];
            $usuario = $entityManager->getRepository(Usuario::class)->find($usuarioid['id']);
            $usuario_array = [
                'nombre' => $usuario->getNombre(),
                'apellido' => $usuario->getApellido(),
            ];

            $asesor = $asesoria->getIdAsesor();

            if ($asesor == null) {
                $data[] = [
                    'id' => $asesoria->getId(),
                    'nombre' => $asesoria->getNombre(),
                    'estado' => $asesoria->getEstado(),
                    'fecha' => $asesoria->getFecha(),
                    'cliente' => $usuario_array
                ];
            }
        }

        return $this->json([
            $generadorDeMensajes->generarRespuesta("Estas son todas las asesorias sin asesores: ", $data),
            'total' => $total,
            'lastPage' => $lastPage,
            'page' => $page,
        ]);
    }
}
