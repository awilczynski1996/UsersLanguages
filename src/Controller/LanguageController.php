<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class LanguageController extends AbstractController
{
    /**
     * @Route("/languages")
     * @Method({"GET"})
     */
    public function listAction()
    {
        $data = [];
        $languages = $this->getDoctrine()->getRepository('App:Language')->findAll();

        foreach ($languages as $language) {
            $data[] = [
                'name' => $language->getLanguage(),
                'count' => $language->getUsers()->count()
            ];
        }

        return $this->render('language/list.html.twig', [
            'data' => $data,
        ]);
    }
}
