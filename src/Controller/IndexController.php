<?php


namespace App\Controller;
use App\Entity\Partner;
use App\Entity\User;
use App\Form\PartnerType;
use App\Form\UserType;
use App\Repository\AdherentRepository;
use Doctrine\Common\Persistence\ObjectManager;
use MKebza\GoogleMaps\Service\StaticMap;
use Symfony\Component\ExpressionLanguage\Tests\Node\Obj;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Entity\Adherent;



class IndexController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */

    public function index(Request $request)
    {
        if(!$request->hasSession())
        {
            $request->getSession()->invalidate(1);
        }

        $session = $request->getSession();

        if( $session->get('adh_id') != null )
        {
            return $this->redirectToRoute("show_adh", ["id" =>$session->get('adh_id')]);
        }

        $adherent = new Adherent();
        $form = $this->createFormBuilder($adherent)
            ->add("matricule", null, ["label" => "Votre N° de Matricule"])
            ->add("birthday", BirthdayType::class, [
                "label" => "Votre date de naissance",
                'widget' => 'choice',
                'format' => 'dd-MM-yyyy',
                'attr' => ['class' => 'js-datepicker'],
                'years' => range(date('Y')-15, date('Y')-65),
            ])
            ->add("submit", SubmitType::class)
            ->getForm();

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $matricule = $adherent->getMatricule(); settype($matricule, "integer");
            $repo = $this->getDoctrine()->getRepository(Adherent::class);
            $adherents = $repo->findOneBy(["matricule" => $matricule, "birthday" => $adherent->getBirthday()]);

            if(!$adherents)
            {
                $this->addFlash("danger", "Nous ne vous avons pas trouvé dans notre base de donnée !");
            }
            else
            {
                $session->set("adh_id", $adherents->getId());
                return $this->redirectToRoute("show_adh", ["id" => $adherents->getId()]);
            }

        }

        return $this->render("accueil.html.twig", [
                "form" => $form->createView()
            ]
        );
    }

    /**
     * @Route("/cgu-rgpd", name="cgu")
     */

    public function cgu()
    {


        return $this->render("cgu.html.twig", [
        ]);
    }



}