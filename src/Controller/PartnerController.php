<?php


namespace App\Controller;

use App\Entity\Partner;
use App\Entity\User;
use App\Entity\Voucher;
use App\Form\PartnerType;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use MKebza\GoogleMaps\Service\StaticMap;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;




class PartnerController extends AbstractController
{

    /**
     * @Route("/admin/partners", name="admin_parts")
     */

    public function index()
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $repo = $this->getDoctrine()->getRepository(Partner::class);
        $partners = $repo->findAll();

        return $this->render("admin/partners/list.html.twig", [
            "partners" => $partners
        ]);
    }


    /**
     * @Route("/admin/partners/add", name="add_partner")
     */

    public function add(Request $request, ObjectManager $manager)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Il faut être administrateur !');
        $partner = new Partner();
        $form = $this->createForm(PartnerType::class, $partner);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {

            // Upload du fichier
            $file = $form->get('logo')->getData();
            $fileName = md5(uniqid()).'.'.$file->guessExtension();

            // Passer du tmp au dossier de destination
            try {
                $file->move($this->getParameter('logos_directory'), $fileName);
            } catch (FileException $e) {
                // ... gestion des erreurs
                $errors = 1;
                $this->addFlash("string", "Votre photo n'a pas pu charger, veuillez réessayer !");
            }
            $partner->setLogo($fileName);

            $manager->persist($partner);
            $manager->flush();
            return $this->redirectToRoute("admin_parts");
        }

        return $this->render("admin/partners/modify.html.twig", [
            "form" => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/partners/{id}/modify/", name="partner_modify")
     */
    public function modify(Partner $partner, Request $request, ObjectManager $manager, StaticMap $map)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Il faut être administrateur !');
        $logo = $partner->getLogo();
        $partner->setLogo('');
        $form = $this->createForm(PartnerType::class, $partner);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            // Contrôle de changememnt de logo
            if($request->get('logo') == '' && $logo != null) { $partner->setLogo($logo);}
            else{
                // Upload du fichier
                $file = $form->get('logo')->getData();
                $fileName = md5(uniqid()).'.'.$file->guessExtension();

                // Passer du tmp au dossier de destination
                try {
                    $file->move($this->getParameter('logos_directory'), $fileName);
                    //unlink($this->getParameter('logos_directory').$logo);
                } catch (FileException $e) {
                    // ... gestion des erreurs
                    $errors = 1;
                    $this->addFlash("string", "Votre photo n'a pas pu charger, veuillez réessayer !");
                }
                $partner->setLogo($fileName);
            }
            $manager->persist($partner);
            $manager->flush();
        }

        return $this->render("admin/partners/modify.html.twig", [
            "form" => $form->createView(),
            "logo" => $logo,
            "partner" => $partner->getDiscount(),
            'id' => $partner->getId()
        ]);
    }



    // Suppression des partenariats

    /**
     * @Route("/admin/partners/{id}/delete", name="delete_partner")
     */
    public function delete($id, ObjectManager $manager)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Il faut être administrateur !');
        $partner = $manager->getRepository(Partner::class)->find($id);

        if (!$partner) {
            throw $this->createNotFoundException(
                'No partner found for id '.$id
            );
        }
        $manager->remove($partner);
        $manager->flush();
        return $this->redirectToRoute('admin_parts');
    }

    // Liste publique

    /**
     * @Route("partners/showall", name="show_partners")
     */
    public function showall()
    {
        $repo = $this->getDoctrine()->getRepository(Partner::class);
        $partners = $repo->findAll();

        return $this->render("partners/partners.html.twig", [
            "partners" => $partners
        ]);
    }

    /**
     * @Route("partners/map", name="partners_map")
     */

    public function map()
    {
        $repo = $this->getDoctrine()->getRepository(Partner::class);
        $partners = $repo->findAll();

        return $this->render("partners/map.html.twig", [
            "partners" => $partners
        ]);
    }

    // Affichage publique d'un partenaire

    /**
     * @Route("partners/{id}/show/", name="show_partner")
     */

    public function show(Partner $partner, ObjectManager $manager,Request $request)
    {

        if(!$request->hasSession())
        {
            $request->getSession()->invalidate(1);
        }

        $session = $request->getSession();

        if( $session->get('adh_id') == null )
        {
            $this->addFlash("danger", "Vous devez être connecté pour accéder aux détails des partenaires !");
            return $this->redirectToRoute("index");
        }

        $escape = $partner->getAddress().$partner->getZipcode().$partner->getCity();
        $escaped = str_replace(" ", "+", $escape);

        $session = $request->getSession();
        if( $session->get('adh_id') != null) {
            $voucher = $manager->getRepository(Voucher::class)->findBy(["partner" => $partner, "adherent" => $session->get('adh_id')]);
        }
        else
        {
            $voucher = "";
        }
        return $this->render("partners/partner.html.twig", [
            "partner" => $partner,
            "escape" => $escaped,
            "vouchers" => $voucher
        ]);
    }

}