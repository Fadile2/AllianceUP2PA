<?php
/**
 * Created by PhpStorm.
 * User: W10Fadile
 * Date: 24/01/2019
 * Time: 14:56
 */

namespace App\Controller;
use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Adherent;


class CardController extends AbstractController
{

    /**
     * @Route("/adherent/{id}/card/generate", name="card_generate")
     */
    public function card(Adherent $adherent)
    {
        $package = new Package(new EmptyVersionStrategy());

        $path1 = $package->getUrl('C:\wamp64\www\AllianceUP2PA\public\carte\bg\fondcarte4.png');
        $ext = explode(".", $adherent->getPicture());
        $paths = "\ "; $str = explode(" ", $paths);
        //$path2 = $package->getUrl('C:\wamp\www\Alliance\public\carte\bg\tete.png');
        $pathc = $this->getParameter('pictures_directory'); $path2 = $pathc.$str[0].$adherent->getPicture();
        $image = imagecreatefrompng($path1);
        if($ext[1] == "png") {
            $image2 = imagecreatefrompng($path2);
        }
        else {
            $image2 = imagecreatefromjpeg($path2);
        }

        $width = imagesx($image2);
        $height = imagesy($image2);


        $newwidth = 280;
        $newheight = 280;

        $image3 = imagecreatetruecolor($newwidth, $newheight);
        imagecopyresized($image3, $image2, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
        //this creates a pink rectangle of the same size
        $mask = imagecreatetruecolor($newwidth, $newheight);
        $pink = imagecolorallocate($mask, 255, 0, 255);
        imagefill($mask, 0, 0, $pink);
        //this cuts a hole in the middle of the pink mask
        $black = imagecolorallocate($mask, 0, 0, 0);
        imagecolortransparent($mask, $black);
        imagefilledellipse($mask, $newwidth/2, $newheight/2, $newwidth, $newheight, $black);
        //this merges the mask over the pic and makes the pink corners transparent
        imagecopymerge($image3, $mask, 0, 0, 0, 0, $newheight, $newheight, 100);
        imagecolortransparent($image3, $pink);

        imagefilledellipse($image, 215, 450, 295, 295, 0xFFFFFF);

        //list($width, $height) = getimagesize($path2);
        imagecopyresized($image, $image3, 75, 310, 0, 0, 280, 280, 280, 280);



        $text_color = imagecolorallocate($image, 0, 0, 0);
        $text_color2 = imagecolorallocate($image, 255, 255, 255);

        $fontfile = $package->getUrl("C:\wamp64\www\AllianceUP2PA\public\Raleway-Black.ttf");
        $name = "Nom : ".$adherent->getName();
        $nb1 = strlen($name);
        $prenom = "Prénom : ".$adherent->getFirstname();
        $nb2 = strlen($prenom);
        $mat = "Matricule n°".$adherent->getMatricule();

        if($nb1  > 24 ) {
            imagettftext($image, 28, 0, 425, 270, $text_color, $fontfile, $name);
        }
        else
        {
            imagettftext ( $image , 34 , 0 , 425 , 270 , $text_color , $fontfile , $name );
        }
        if($nb2  > 24 )
        {
            imagettftext ( $image , 28 , 0 , 425 , 320 , $text_color , $fontfile , $prenom );
        }
        else {
            imagettftext ( $image , 34 , 0 , 425 , 320 , $text_color , $fontfile , $prenom );
        }
        imagettftext ( $image , 38 , 0 , 425 , 420 , $text_color , $fontfile , "2019/2020" );
        imagettftext ( $image , 18 , 0 , 100 , 80 , $text_color2 , $fontfile , $mat );

        ob_start();
        imagepng($image);
        $str_img = ob_get_contents();
        ob_end_clean();
        $headers = array('Content-Type' => 'image/png','Content-Disposition' => 'inline; filename="image.png"');
        return new Response($str_img, 200, $headers);
    }


}