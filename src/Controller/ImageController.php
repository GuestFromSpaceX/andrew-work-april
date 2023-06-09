<?php

namespace App\Controller;

use App\Entity\Image;
use App\Entity\User;
use App\Form\ImageType;
use Doctrine\ORM\EntityManagerInterface;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ImageController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

        // Обрабатывает запросы GET и POST по адресу /images/upload
        #[Route('/images/upload/{user_id}', name: 'image_upload', methods: ['GET', 'POST'])]
        public function upload(Request $request, int $user_id, UploaderHelper $helper): Response
        {
            // Создаем новый экземпляр Image
            $image = new Image();
            
            // Получаем пользователя по его ID и связываем его с изображением
            $user = $this->entityManager->getRepository(User::class)->find($user_id);
            $image->setRelation($user);
    
            // Создаем форму с использованием класса ImageType
            $form = $this->createForm(ImageType::class, $image);
    
            // Обработчик формы проверяет, была ли отправлена форма и валидна ли она
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                // Получаем путь к загруженному изображению
                $imagePath = $form['imageFile']->getData()->getRealPath();
    
                // Устанавливаем путь к изображению
                $image->setImagePath($imagePath);
    
                // Сохраняем изображение
                $this->entityManager->persist($image);
                $this->entityManager->flush();
    
                // Перенаправляем пользователя на страницу изображения и передаем идентификатор созданного изображения
                return $this->redirectToRoute('image_show', ['id' => $image->getId()]);
            }
    
            // Отображаем форму загрузки изображения
            return $this->render('image/upload.html.twig', [
                'form' => $form->createView(),
                'user_id' => $user_id, // передаем в шаблон
            ]);
        }

    #[Route('/image/{id}', name: 'image_show', methods: ['GET'])]
    public function show(Image $image): Response
    {
        return $this->render('image/show.html.twig', [
            'image' => $image,
        ]);
    }
}

