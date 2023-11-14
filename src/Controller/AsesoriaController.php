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
    #[Route('/{filtro}', name: 'app_read_all_asesorias', methods: ['GET'])]
    public function read(EntityManagerInterface $entityManager, Request $request, GeneradorDeMensajes $generadorDeMensajes, string $filtro = null): JsonResponse
    {
        if ($filtro == "all" ||  $filtro == "s" || $filtro == "t" ||  $filtro == "e" || $filtro == null) {
            $repositorio = $entityManager->getRepository(Asesoria::class);

            $limit = 20;

            $page = $request->get('page', 1);

            $asesorias = $repositorio->findAllWithPagination($page, $limit);

            $total = $asesorias->count();

            $lastPage = (int) ceil($total / $limit);

            $data = [];

            if ($filtro == "all" || $filtro == null) {
                foreach ($asesorias as $asesoria) {

                    $usuarioid = ['id' => $asesoria->getIdCliente()];
                    $usuario = $entityManager->getRepository(Usuario::class)->find($usuarioid['id']);
                    $usuario_array = [
                        'nombre' => $usuario->getNombre(),
                        'apellido' => $usuario->getApellido(),
                    ];

                    $asesor = ['id' => $asesoria->getIdAsesor()];
                    $asesor = $entityManager->getRepository(Usuario::class)->find($usuarioid['id']);
                    $asesor_array = [
                        'nombre' => $asesor->getNombre(),
                        'apellido' => $asesor->getApellido(),
                    ];

                    if ($asesor == null) {
                        $data[] = [
                            'id' => $asesoria->getId(),
                            'nombreAsesoria' => $asesoria->getNombre(),
                            'estado' => $asesoria->getEstado(),
                            'fecha' => $asesoria->getFecha(),
                            'cliente' => $usuario_array
                        ];
                    } else {
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
                return $this->json([
                    $generadorDeMensajes->generarRespuesta("Estas son todas las asesorias: ", $data),
                    'total' => $total,
                    'lastPage' => $lastPage,
                    'page' => $page,
                ]);
            } else {
                if ($filtro == "s") {
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
                        $generadorDeMensajes->generarRespuesta("Estas son todas las asesorias sin asesor: ", $data),
                        'total' => $total,
                        'lastPage' => $lastPage,
                        'page' => $page,
                    ]);
                } else if ($filtro == "t") {
                    foreach ($asesorias as $asesoria) {

                        $usuarioid = ['id' => $asesoria->getIdCliente()];
                        $usuario = $entityManager->getRepository(Usuario::class)->find($usuarioid['id']);
                        $usuario_array = [
                            'nombre' => $usuario->getNombre(),
                            'apellido' => $usuario->getApellido(),
                        ];

                        $asesor = ['id' => $asesoria->getIdAsesor()];
                        $asesor = $entityManager->getRepository(Usuario::class)->find($usuarioid['id']);
                        $asesor_array = [
                            'nombre' => $asesor->getNombre(),
                            'apellido' => $asesor->getApellido(),
                        ];

                        $estado = $asesoria->getEstado();

                        if ($estado == "terminado") {
                            if ($asesor == null) {
                                $data[] = [
                                    'id' => $asesoria->getId(),
                                    'nombreAsesoria' => $asesoria->getNombre(),
                                    'estado' => $asesoria->getEstado(),
                                    'fecha' => $asesoria->getFecha(),
                                    'cliente' => $usuario_array
                                ];
                            } else {
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
                    }
                    return $this->json([
                        $generadorDeMensajes->generarRespuesta("Estas son todas las asesorias terminadas: ", $data),
                        'total' => $total,
                        'lastPage' => $lastPage,
                        'page' => $page,
                    ]);
                } else if ($filtro == "e") {
                    foreach ($asesorias as $asesoria) {

                        $usuarioid = ['id' => $asesoria->getIdCliente()];
                        $usuario = $entityManager->getRepository(Usuario::class)->find($usuarioid['id']);
                        $usuario_array = [
                            'nombre' => $usuario->getNombre(),
                            'apellido' => $usuario->getApellido(),
                        ];

                        $asesor = ['id' => $asesoria->getIdAsesor()];
                        $asesor = $entityManager->getRepository(Usuario::class)->find($usuarioid['id']);
                        $asesor_array = [
                            'nombre' => $asesor->getNombre(),
                            'apellido' => $asesor->getApellido(),
                        ];

                        $estado = $asesoria->getEstado();

                        if ($estado == "en proceso") {
                            if ($asesor == null) {
                                $data[] = [
                                    'id' => $asesoria->getId(),
                                    'nombreAsesoria' => $asesoria->getNombre(),
                                    'estado' => $asesoria->getEstado(),
                                    'fecha' => $asesoria->getFecha(),
                                    'cliente' => $usuario_array
                                ];
                            } else {
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
                    }
                    return $this->json([
                        $generadorDeMensajes->generarRespuesta("Estas son todas las asesorias en proceso: ", $data),
                        'total' => $total,
                        'lastPage' => $lastPage,
                        'page' => $page,
                    ]);
                }
            }
        } else {
            return $this->json(([$generadorDeMensajes->generarRespuesta("error 404 pagina no encontrada")]));
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
