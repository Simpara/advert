<?php

// src/OC/PlatformBundle/Controller/AdvertController.php

namespace OC\PlatformBundle\Controller;


use OC\PlatformBundle\Entity\Advert;
use OC\PlatformBundle\Entity\Image;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class AdvertController extends Controller
{
	public function indexAction($page)
	{
		// On ne sait pas combien de pages il y a
		// Mais on sait qu'une page doit être supérieure ou égale à 1
		if ($page < 1) {
			// On déclenche une exception NotFoundHttpException, cela va afficher
			// une page d'erreur 404 (qu'on pourra personnaliser plus tard d'ailleurs)
			throw new NotFoundHttpException('Page "'.$page.'" inexistante.');
		}

		// Dans l'action indexAction() :
		return $this->render('OCPlatformBundle:Advert:index.html.twig', array(
				'listAdverts' => array()
		));
	}

	public function viewAction($id)
	{
		// On récupère le repository
		$repository = $this->getDoctrine()
		->getManager()
		->getRepository('OCPlatformBundle:Advert')
		;

		// On récupère l'entité correspondante à l'id $id
		$advert = $repository->find($id);

		// $advert est donc une instance de OC\PlatformBundle\Entity\Advert
		// ou null si l'id $id  n'existe pas, d'où ce if :
		if (null === $advert) {
			throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
		}

		// Le render ne change pas, on passait avant un tableau, maintenant un objet
		return $this->render('OCPlatformBundle:Advert:view.html.twig', array(
				'advert' => $advert
		));
	}

	public function addAction(Request $request)
	{
		// Création de l'entité Advert
		$advert = new Advert();
		$advert->setDate(new Date());
		$advert->setTitle('Recherche développeur Symfony.');
		$advert->setAuthor('Alexandre');
		$advert->setContent("Nous recherchons un développeur Symfony débutant sur Lyon. Blabla…");

		// Création de l'entité Image
		$image = new Image();
		$image->setUrl('http://sdz-upload.s3.amazonaws.com/prod/upload/job-de-reve.jpg');
		$image->setAlt('Job de rêve');

		// On lie l'image à l'annonce
		$advert->setImage($image);

		// On récupère l'EntityManager
		$em = $this->getDoctrine()->getManager();

		// Étape 1 : On « persiste » l'entité
		$em->persist($advert);

		// Étape 1 bis : si on n'avait pas défini le cascade={"persist"},
		// on devrait persister à la main l'entité $image
		// $em->persist($image);

		// Étape 2 : On déclenche l'enregistrement
		$em->flush();

		// Reste de la méthode qu'on avait déjà écrit
		if ($request->isMethod('POST')) {
			$request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');

			// Puis on redirige vers la page de visualisation de cettte annonce
			return $this->redirectToRoute('oc_platform_view', array('id' => $advert->getId()));
		}

		// Si on n'est pas en POST, alors on affiche le formulaire
		return $this->render('OCPlatformBundle:Advert:add.html.twig', array('advert' => $advert));
	}

	public function editImageAction($advertId)
	{
		$em = $this->getDoctrine()->getManager();

		// On récupère l'annonce
		$advert = $em->getRepository('OCPlatformBundle:Advert')->find($advertId);

		// On modifie l'URL de l'image par exemple
		$advert->getImage()->setUrl('test.png');

		// On n'a pas besoin de persister l'annonce ni l'image.
		// Rappelez-vous, ces entités sont automatiquement persistées car
		// on les a récupérées depuis Doctrine lui-même

		// On déclenche la modification
		$em->flush();

		return new Response('OK');
	}

	public function deleteAction($id)
	{
		// Ici, on récupérera l'annonce correspondant à $id

		// Ici, on gérera la suppression de l'annonce en question

		return $this->render('OCPlatformBundle:Advert:delete.html.twig');
	}
	public function menuAction($limit)
	{
		// On fixe en dur une liste ici, bien entendu par la suite
		// on la récupérera depuis la BDD !
		$listAdverts = array(
				array('id' => 2, 'title' => 'Recherche développeur Symfony'),
				array('id' => 5, 'title' => 'Mission de webmaster'),
				array('id' => 9, 'title' => 'Offre de stage webdesigner')
		);

		return $this->render('OCPlatformBundle:Advert:menu.html.twig', array(
				// Tout l'intérêt est ici : le contrôleur passe
				// les variables nécessaires au template !
				'listAdverts' => $listAdverts
		));
	}
}