<?php
/**
 * Created by PhpStorm.
 * User: W10Fadile
 * Date: 24/01/2019
 * Time: 14:56
 */

namespace App\Controller;
use App\Form\AdherentType;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Adherent;


class AdherentController extends AbstractController
{

    // Ajouter un adhérent
    /**
     * @Route("/adherents/add", name="add_adh")
     */
    public function add(Request $request, ObjectManager $manager)
    {
        $adherent = new Adherent();
        $form = $this->createForm(AdherentType::class, $adherent);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            // Enregistrements par défaut
            $adherent->setStatus(1);
            $adherent->setSubcriptionDate(new \DateTime());
            $errors = 0;

            // Upload du fichier
            $file = $form->get('picture')->getData();
            $fileName = md5(uniqid()).'.'.$file->guessExtension();

            // Passer du tmp au dossier de destination
            try {
                $file->move($this->getParameter('pictures_directory'), $fileName);
            } catch (FileException $e) {
                // ... gestion des erreurs
                $errors = 1;
                $this->addFlash("string", "Votre photo n'a pas pu charger, veuillez réessayer !");
            }
            $adherent->setPicture($fileName);

            if($errors == 0) {
                // Enregistrement dans la base de données
                $manager->persist($adherent);
                $manager->flush();
                return $this->redirectToRoute('show_adh', ['id' => $adherent->getId()]);
            }
        }

        return $this->render("adherents/adhform.html.twig", [
            "form" => $form->createView(),
            "titre" => "Créer ma carte Alliance"
        ]);

    }

    // Vu d'un adhérent
    /**
     * @Route("/adherents/{id}/show", name="show_adh")
     */
    public function show(Adherent $adherent, Request $request)
    {
        $session = $request->getSession();
        if( $session->get('adh_id') != null && $session->get('adh_id') ==  $adherent->getId())
        {
            $resultat = $this->render("adherents/show.html.twig", [
                "adh" => $adherent
            ]);
        }
        elseif($this->getUser() != null)
        {
            $resultat = $this->render("show.html.twig", [
                "adh" => $adherent
            ]);
        }
        else{
             $resultat = $this->redirectToRoute("index");
        }

        return $resultat;
    }

    // Modifier un adherent
    /**
     * @Route("/adherents/{id}/edit", name="edit_adh")
     */
    public function edit(Adherent $adherent, Request $request, ObjectManager $manager)
    {
        $session = $request->getSession();
        // Gestion de l'exception de la photo
        $picture = $adherent->getPicture();
        $adherent->setPicture('');
        //Formulaire
        $form = $this->createFormBuilder($adherent)
            ->add('name')
            ->add('firstname')
            ->add('birthday', BirthdayType::class)
            ->add('email',EmailType::class)
            ->add('matricule', null, [
                "disabled" => true
            ])
            ->add('gdpr_consent', CheckboxType::class, [
                "disabled" => true
            ])
            ->add('picture', FileType::class, [
                'required' => false,
                'empty_data' => ''
            ])
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            // Contrôle de changememnt de la photo
            if($request->get('picture') == '' && $picture != null) { $adherent->setPicture($picture);}
            else{
                // Upload du fichier
                $file = $form->get('picture')->getData();
                $fileName = md5(uniqid()).'.'.$file->guessExtension();

                // Passer du tmp au dossier de destination
                try {
                    $file->move($this->getParameter('pictures_directory'), $fileName);
                } catch (FileException $e) {
                    // ... gestion des erreurs
                    $this->addFlash("error", "Votre photo n'a pas pu charger, veuillez réessayer !");
                }
                $adherent->setPicture($fileName);
            }

            // Enregistrement
            $adherent->setStatus(1);
            $manager->persist($adherent);
            $manager->flush();
        }

        // Accès si l'adhérent est connectée
        if( $session->get('adh_id') != null && $session->get('adh_id') ==  $adherent->getId()) {

            return $this->render("adherents/adhform.html.twig", [
                "form" => $form->createView(),
                "pict" => $picture,
                "titre" => "Edition des informations"
            ]);

        }
        // Accès si un administrateur est connecté
        elseif($this->getUser() != null)
        {
            return $this->render("adherents/adhform.html.twig", [
                "form" => $form->createView(),
                "pict" => $picture,
                "titre" => "Edition des informations"
            ]);
        }
        else{
            return $this->redirectToRoute("index");
        }

    }

    // Emploi du temps
    /**
     * @Route("/adherents/{id}/planning", name="planning")
     */
    public function planning(Adherent $adherent, Request $request)
    {
        $session = $request->getSession();
        if( $session->get('adh_id') == null | $session->get('adh_id') !=  $adherent->getId())
        {
            return $this->redirectToRoute("index");
        }
        $orgdate = $adherent->getBirthday();
        $date = $orgdate->format('dmY');

        $url = "https://esn.paris/TestAssas/planning4.php?matricule=".$adherent->getMatricule()."&naissance=".$date."&token=All19";
        $content = file_get_contents($url);
        $json = json_decode(utf8_encode($content), true);
        //$count = count($json);
        return $this->render("adherents/planning.html.twig", [
            "tab" => $json,
            "first" => $json[1]
        ]);
    }

    // Déconnexion
    /**
     * @Route("/adherents/disconnect", name="end")
     */
    public function end(Request $request)
    {
        $session = $request->getSession();
        $session->remove("adh_id");
        return $this->redirectToRoute("index");
    }

}