<?php

namespace App\Controller;

use App\Entity\Listing;
use App\Entity\Task;
use App\Form\TaskType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("{listingId}/task", name="task_", requirements={"listingId"="\d+"})
 */
class TaskController extends AbstractController {

    /**
     * @Route("/new", name="create")
     */
    public function create($listingId, EntityManagerInterface $em, Request $req){
        $listing = $em->find(Listing::class, $listingId);
        if(empty($listing)) {
            $this->addFlash('warning', 'Aucune liste avec cet identifiant');
            return $this->redirectToRoute('listing_show');
        }
        $task = new Task();
        $task->setListing($listing);
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($req);

        if($form->isSubmitted() && $form->isValid()){                            
            $name = $task->getName();
            $em->persist($task);
            $em->flush();
            $this->addFlash('success',"La tâche « $name » a bien été ajoutée");
            return $this->redirectToRoute('listing_show' ,[
                'listingId' => $listingId
            ]);            
        }      
        return $this->render('task.html.twig' , [
            'form' => $form->createView()
            ]);        
    } 

    /**
     * @Route("/{taskId}/delete" , name="remove", requirements={"taskId"="\d+"})
     */
    public function delete($listingId , $taskId, EntityManagerInterface $em){
        $listing = $em->find(Listing::class, $listingId);
        if(empty($listing)){
            $this->addFlash('warning', "La tâche que vous tentez d'effacer n'existe pas");
            $this->redirectToRoute('listing_show');
        }
        else {
            $task = $em->find(Task::class, $taskId);
            $name = $task->getName();
            $em->remove($task);
            $em->flush();
            $this->addFlash('success', "La tâche « $name » a été supprimée avec succès");
            return $this->redirectToRoute('listing_show' , [
                "listingId" => $listingId
            ]);
        }
    }
}