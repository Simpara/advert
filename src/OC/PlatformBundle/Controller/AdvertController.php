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
		// Mais on sait qu'une page doit �tre sup�rieure ou �gale � 1
		if ($page < 1) {
			// On d�clenche une exception NotFoundHttpException, cela va afficher
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
		// On r�cup�re le repository
		$repository = $this->getDoctrine()
		->getManager()
		->getRepository('OCPlatformBundle:Advert')
		;

		// On r�cup�re l'entit� correspondante � l'id $id
		$advert = $repository->find($id);

		// $advert est donc une instance de OC\PlatformBundle\Entity\Advert
		// ou null si l'id $id  n'existe pas, d'o� ce if :
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
		// Cr�ation de l'entit� Advert
		$advert = new Advert();
		$advert->setDate(new Date());
		$advert->setTitle('Recherche d�veloppeur Symfony.');
		$advert->setAuthor('Alexandre');
		$advert->setContent("Nous recherchons un d�veloppeur Symfony d�butant sur Lyon. Blabla�");

		// Cr�ation de l'entit� Image
		$image = new Image();
		$image->setUrl('http://sdz-upload.s3.amazonaws.com/prod/upload/job-de-reve.jpg');
		$image->setAlt('Job de r�ve');

		// On lie l'image � l'annonce
		$advert->setImage($image);

		// On r�cup�re l'EntityManager
		$em = $this->getDoctrine()->getManager();

		// �tape 1 : On � persiste � l'entit�
		$em->persist($advert);

		// �tape 1 bis : si on n'avait pas d�fini le cascade={"persist"},
		// on devrait persister � la main l'entit� $image
		// $em->persist($image);

		// �tape 2 : On d�clenche l'enregistrement
		$em->flush();

		// Reste de la m�thode qu'on avait d�j� �crit
		if ($request->isMethod('POST')) {
			$request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistr�e.');

			// Puis on redirige vers la page de visualisation de cettte annonce
			return $this->redirectToRoute('oc_platform_view', array('id' => $advert->getId()));
		}

		// Si on n'est pas en POST, alors on affiche le formulaire
		return $this->render('OCPlatformBundle:Advert:add.html.twig', array('advert' => $advert));
	}

	public function editImageAction($advertId)
	{
		$em = $this->getDoctrine()->getManager();

		// On r�cup�re l'annonce
		$advert = $em->getRepository('OCPlatformBundle:Advert')->find($advertId);

		// On modifie l'URL de l'image par exemple
		$advert->getImage()->setUrl('test.png');

		// On n'a pas besoin de persister l'annonce ni l'image.
		// Rappelez-vous, ces entit�s sont automatiquement persist�es car
		// on les a r�cup�r�es depuis Doctrine lui-m�me

		// On d�clenche la modification
		$em->flush();

		return new Response('OK');
	}

	public function deleteAction($id)
	{
		// Ici, on r�cup�rera l'annonce correspondant � $id

		// Ici, on g�rera la suppression de l'annonce en question

		return $this->render('OCPlatformBundle:Advert:delete.html.twig');
	}
	public function menuAction($limit)
	{
		// On fixe en dur une liste ici, bien entendu par la suite
		// on la r�cup�rera depuis la BDD !
		$listAdverts = array(
				array('id' => 2, 'title' => 'Recherche d�veloppeur Symfony'),
				array('id' => 5, 'title' => 'Mission de webmaster'),
				array('id' => 9, 'title' => 'Offre de stage webdesigner')
		);

		return $this->render('OCPlatformBundle:Advert:menu.html.twig', array(
				// Tout l'int�r�t est ici : le contr�leur passe
				// les variables n�cessaires au template !
				'listAdverts' => $listAdverts
		));
	}
}