<?php

namespace App\Controller;

use App\Entity\DataSet;
use App\Form\DataSetDailyType;
use App\Form\DataSetMonthlyType;
use App\Form\DataSetType;
use App\Form\DataSetWeeklyType;
use App\Form\Filter\DataSetFilterType;
use App\Service\DataStoreProvider;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\VarDumper\Cloner\Data;


/**
 * Filetosync controller.
 *
 */
class DataSetController extends CrudController
{
    protected $filter = DataSetFilterType::class;

    protected function getFilterDefaultValues()
    {
        return [
        ];
    }


    public function create(RequestStack $request)
    {
        $dataset = new DataSet();

        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(DataSetType::class, $dataset);

        $form->handleRequest($request->getCurrentRequest());

        if ($form->isSubmitted() && $form->isValid()){
            $dataset
                ->setPeriodStart($form->getNormData()->getPeriodStart())
                ->setPeriodEnd($form->getNormData()->getPeriodEnd());
            $dataset->setType($form->getNormData()->getType());
            $dataset->setActive(false);
            $dataset->setUpload($form->getNormData()->getUpload());
            $dataset->getPeriodStart()->setTime(0, 0, 0);
            $dataset->getPeriodEnd()->setTime(0, 0, 0);

            $em->persist($dataset);
            $em->flush();

            if ($dataset->getUpload()) {
                $em->getRepository('App:DataSet')
                    ->disableUploadOthers($dataset->getId(), $dataset->getType());
            }
            $this->addFlash('success', 'Dataset created.');
            return $this->redirectToRoute('dataset_index');
        }

        return $this->render('dataset/create.html.twig', [
            'form' => $form->createView()
        ]);
    }


    public function edit(DataSet $dataset, RequestStack $request)
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(DataSetType::class, $dataset);

        $form->handleRequest($request->getCurrentRequest());

        if ($form->isSubmitted() && $form->isValid()){

            $dataset
                ->setPeriodStart($form->getNormData()->getPeriodStart())
                ->setPeriodEnd($form->getNormData()->getPeriodEnd());
            $dataset->setType($form->getNormData()->getType());
            $dataset->setActive($form->getNormData()->getActive());
            $dataset->setUpload($form->getNormData()->getUpload());
            $dataset->getPeriodStart()->setTime(0, 0, 0);
            $dataset->getPeriodEnd()->setTime(0, 0, 0);
            $em->persist($dataset);
            $em->flush();

            if ($dataset->getUpload()){
                $em->getRepository('App:DataSet')
                   ->disableUploadOthers($dataset->getId(), $dataset->getType());
            }



            $this->addFlash('success', 'Dataset created.');
            return $this->redirectToRoute('dataset_index');
        }


        return $this->render('dataset/update.html.twig', [
            'form' => $form->createView()
        ]);

    }


}
