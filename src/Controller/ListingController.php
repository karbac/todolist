<?php

namespace App\Controller;

use App\Entity\Listing;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/", name="listing_")
 */
class ListingController extends AbstractController{
    
    /**
     * @Route("/{listingId}" , name="show", requirements={"listingId"="\d+"})
     */
    public function show(EntityManagerInterface $em , $listingId=null) {
        $listings = $em->getRepository(Listing::class)->findAll();
        if(!empty($listingId)){
            $currentListing = $em->find(Listing::class, $listingId);
        }
        if(empty($currentListing)){
            $currentListing = current($listings);
        }
        return $this->render('listing.html.twig' , [
            'listings' => $listings,
            'currentListing' => $currentListing
            ]);
    }

    /**
     * @Route("/new", methods="POST", name="create")
     */
    public function create(EntityManagerInterface $em , Request $req) {
        $name = $req->get('name');
        if (empty($name)){
            $this->addFlash('warning','Aucun nom de liste saisi');
            return $this->redirectToRoute('listing_show');                  
        }

        $listing = new Listing();
        $listing->setName($name);
        try {    
            $em->persist($listing);
            $em->flush();
            $this->addFlash('success', "La liste « $name » a bien été créée");
        } catch (UniqueConstraintViolationException $e) {
            $this->addFlash('warning',"La liste « $name » existe déjà");
        } finally {
            return $this->redirectToRoute('listing_show');
        }        
    }

    /**
     * @Route("/{listingId}/delete" , name="remove" , requirements={"listingId"="\d+"})
     */
    public function delete(EntityManagerInterface $em , $listingId) {
        $listing = $em->find(Listing::class, $listingId);
        if(empty($listing)) {
            $this->addFlash('warning' , 'Impossible de supprimer cette liste');
        }
        else {
            $name = $listing->getName();
            $em->remove($listing);
            $em->flush();
            $this->addFlash('success' , "La liste « $name » a été supprimée avec succès");
        }
        return $this->redirectToRoute('listing_show');        
    }
}