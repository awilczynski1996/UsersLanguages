<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormBuilder;

class UserController extends AbstractController
{
    /**
     * @Route("/users", name="list")
     * @Method ({"GET", "POST"})
     */
    public function listAction(Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('last_day', ChoiceType::class, [
                'choices' => [
                    3 => $date = date('Y-m-d', strtotime('-3 days')),
                    7 => $date = date('Y-m-d', strtotime('-7 days')),
                    30 => $date = date('Y-m-d', strtotime('-30 days')),
                ]
            ])
            ->add('save', SubmitType::class, ['label' => 'Show', 'attr' => ['class' => 'btn btn-primary mt-3']])
            ->getForm();

        $form->handleRequest($request);
        $form->isSubmitted() ? $date = $form->getData()['last_day'] : $date = null;

        $data = [];

        $users = $this->getDoctrine()->getRepository('App:User')->search($date);

        foreach ($users as $user) {
            $programmingLanguages = [];
            foreach ($user->getLanguages() as $key => $language) {
                $programmingLanguages[] = $language->getLanguage();
            }

            $languages = implode(', ', $programmingLanguages);

            $pesel = $user->getPesel();

            if (substr($pesel, 2, 2) < 20) {
                $data[] = [
                    'name' => $user->getName(),
                    'surname' => $user->getSurname(),
                    'languages' => $languages,
                    'age' => date('Y') - (1900 + substr($pesel, 0, 2)),
                ];
            } elseif (substr($pesel, 2, 2) > 20) {
                if (date('Y') - (2000 + substr($pesel, 0, 2)) >= 18) {

                    $data[] = [
                        'name' => $user->getName(),
                        'surname' => $user->getSurname(),
                        'languages' => $languages,
                        'age' => date('Y') - (2000 + substr($pesel, 0, 2)),
                    ];

                } else {
                    $birthYear = 2000 + substr($pesel, 0, 2);
                    $birthMonth = substr($pesel, 2, 2) - 20;
                    $birthDay = substr($pesel, 4, 2);

                    $nextYear = date('Y') + 1;
                    $tempDate = date($nextYear . '-' . $birthMonth . '-' . $birthDay);
                    $date1 = new \DateTime($tempDate);
                    $date2 = new \DateTime();
                    $diff = $date1->diff($date2, true);

                    $restYears = 18 - (date('Y') - $birthYear + 1);

                    $data[] = [
                        'name' => $user->getName(),
                        'surname' => $user->getSurname(),
                        'languages' => $languages,
                        'age' => 'Pełnoletność za: ' . $restYears . ' lat i ' . $diff->format('%a') . ' dni'
                    ];
                }
            }
        }

        return $this->render('user/list.html.twig', [
            'form' => $form->createView(),
            'users' => $data,
        ]);
    }

    /**
     * @Route("/users/create")
     * @Method ({"POST"})
     */
    public function createAction(Request $request)
    {
        $builder = $this->createFormBuilder()
            ->add('name', TextType::class, ['attr' => ['class' => 'form-control']])
            ->add('surname', TextType::class, ['attr' => ['class' => 'form-control']])
            ->add('email', TextType::class, ['attr' => ['class' => 'form-control']])
            ->add('pesel', TextType::class, ['attr' => ['class' => 'form-control']])
            ->add('languages', TextType::class, ['attr' => ['class' => 'form-control']])
            ->add('save', SubmitType::class, ['label' => 'Create', 'attr' => ['class' => 'form-control']])
            ->getForm();

        $builder->handleRequest($request);

        if ($builder->isSubmitted() && $builder->isValid()) {
            $data = $builder->getData();
            $data['languages'] = explode(', ', strtolower($data['languages']));

            foreach ($data['languages'] as $key => $language) {
                if (!$this->getDoctrine()->getRepository('App:Language')->findOneBy(['language' => $language])) {
                    $this->getDoctrine()->getRepository('App:Language')->create($language);
                }
                $data['languageObjects'][] = $this->getDoctrine()->getRepository('App:Language')->findOneBy(['language' => $language]);
            }

            $this->getDoctrine()->getRepository('App:User')->create($data);
            return $this->redirectToRoute('list');
        }

        return $this->render('user/create.html.twig', [
            'form' => $builder->createView()
        ]);
    }
}
