<?php


namespace App\Controller;
use App\Entity\Partner;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\AdherentRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Entity\Adherent;


//@Security("is_granted('ROLE_SUPER_ADMIN')", statusCode=404, message="Resource not found.")

class AdminController extends AbstractController
{

    /**
     * @Route("/admin", name="admin")
     */

    public function index()
    {
        return $this->render("accueil.html.twig");
    }

    /**
     * @Route("/admin/adherents/list", name="adhs_list")
     */
    public function listAdh()
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Il faut être administrateur !');
        $repo = $this->getDoctrine()->getRepository(Adherent::class);
        $adherents = $repo->findAll();

        return $this->render("admin/adherents/list.html.twig", [
            "adhs" => $adherents
        ]);
    }

    // Exportation de la liste des emails

    /**
     * @Route("/admin/users/list", name="list_user")
     */
    public function listUser()
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $repo = $this->getDoctrine()->getRepository(User::class);
        $user = $repo->findAll();

        return $this->render("admin/users/list.html.twig", [
            "user" => $user
        ]);
    }

    // Gestion de droit des utilisateurs


    // Afficher son profile et le modifier

    /**
     * @Route("/admin/users/{id}/edit", name="user_modify")
     */
    public function modifyUser(User $user, Request $request, ObjectManager $manager)
    {
        $mdp = $user->getPassword();
        $user->setPassword("ABCDEFGH");
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($mdp);
            $manager->persist($user);
            $manager->flush();
        }

        return $this->render("admin/users/modify.html.twig", [
            "form" => $form->createView(),
            "roles" => $user->getRoles(),
            "id" => $user->getId()
        ]);
    }

    // Suppresion d'un adherent
    /**
     * @Route("admin/adherents/{id}/delete", name="delete_adh")
     */
    public function delete($id, ObjectManager $manager)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Il faut être administrateur !');
        $adh = $manager->getRepository(Adherent::class)->find($id);

        if (!$adh) {
            throw $this->createNotFoundException(
                'No student found for id '.$id
            );
        }
        $manager->remove($adh);
        $manager->flush();
        return $this->redirectToRoute('adhs_list');
    }


    // Gestion de gabarit de la carte


}