<?php
/**
 * Created by PhpStorm.
 * User: jochen
 * Date: 14/11/16
 * Time: 11:56 AM
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;


class CrudController extends Controller
{
    protected $queryBuilder;
    protected $request;


    protected function getPager($page)
    {
        $em = $this->getDoctrine()->getManager();

        $repo = $em->getRepository('AppBundle:'.$this->entityName);
        $this->queryBuilder = $repo->createQueryBuilder('p', false);
        $this->queryBuilder->orderBy($this->orderBy[0], $this->orderBy[1]);

        $pager = new Pagerfanta(new DoctrineORMAdapter($this->queryBuilder));
        $pager->setMaxPerPage(50);

        try {
            $pager->setCurrentPage($page);
            $this->get('session')->set($this->entityName.'lastPage', $page);

        } catch (NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }
        return $pager;
    }


/*    public function redirect(string $url, int $status = 302)

    {
        $env = $this->container->get( 'kernel' )->getEnvironment();
        if ($env != 'prod' && !strstr( $url, 'app_' . $env )) {
            $url = '/app_' . $env . '.php' . $url;
        }
        return parent::redirect( $url, $status );
    }
*/

    public function archiveallAction()
    {
        $em = $this->getDoctrine()->getManager();
        $sql = "update " . $this->getTableName() . " set archived=left(md5(id),4) where archived = 'uuu'";
        $em->getConnection()->exec( $sql );

        return parent::redirect( $this->getRoutePrefix().'/page/' . $this->get( 'session' )->get( 'lastPage' ), 302 );
    }


    public function unarchiveallAction()
    {
        $em = $this->getDoctrine()->getManager();
        $sql = "update " . $this->getTableName() . " set archived='uuu' where archived <> 'uuu'";
        $em->getConnection()->exec( $sql );

        return parent::redirect( $this->getRoutePrefix().'/page/' . $this->get( 'session' )->get( 'lastPage' ), 302 );
    }


    public function archiveselectedAction()
    {
        $request = $this->get( 'request' );
        $csrf = $this->get('security.csrf.token_manager');
        if (!$csrf->isCsrfTokenValid( 'archive', $request->request->get( '_csrfToken' ) )) {
            throw new AccessDeniedException();
        }

        if ($request->request->get( 'item' ) &&
            is_array( $request->request->get( 'item' ) )
        ) {
            $sql = "update " . $this->getTableName() . " ";
            if ($request->request->get( 'action' ) == 'archive') {
                $sql .= 'set archived=left(md5(id),4) ';
            } else {
                if ($request->request->get( 'action' ) == 'unarchive') {
                    $sql .= "set archived='uuu' ";
                } else {
                    $sql .= "set archived=achived ";
                }
            }
            $sql .= " where id in (" . implode( ',', array_map( 'intval', array_keys( $request->request->get( 'item' ) ) ) ) . ")";
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->exec( $sql );
        }

        return new Response();
    }


    protected function checkSecurity()
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $checker = $this->get('security.authorization_checker');

        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN', null, 'Unable to access this page!');
    }


    protected function filterFormSetup(&$filterForm)
    {
        //anything that the filter needs, can be overwritten in a child
    }


    protected function getFilterDefaultValues()
    {
        return [];
    }

    protected function getStoredFilterData()
    {
        if ($this->filter) {
            $filterClass = $this->filter;
            $sessionValue = substr($filterClass, strrpos($filterClass, '\\') + 1) . '_filter_value';
            return $this->get('session')->get($sessionValue);
        }else{
            return $this->getFilterDefaultValues();
        }
    }


    protected function renderFilter( $queryBuilder )
    {
        if ($this->filter) {
            $filterClass = $this->filter;
            $filterType = new $filterClass();
            $this->filterFormSetup($filterType);

            $form = $this->get('form.factory')->create($this->filter);
            $sessionValue = substr($filterClass, strrpos($filterClass, '\\')+1).'_filter_value';
            if ($this->request->request->has($form->getName())) {
                $form->submit($this->request->request->get($form->getName()));
            }else{
                $form->setData($this->getStoredFilterData());
            }
            $this->get('session')->set($sessionValue, $form->getData());
            $this->get('lexik_form_filter.query_builder_updater')->addFilterConditions($form, $queryBuilder);

            return $form->createView();
        }
    }


    protected function explainUploadError($errorNo)
    {
        $errors = array(
            UPLOAD_ERR_OK => 'Ok',
            UPLOAD_ERR_INI_SIZE => 'File too big (check upload_max_filesize)',
            UPLOAD_ERR_FORM_SIZE => 'File too big (check MAX_FILE_SIZE in form)',
            UPLOAD_ERR_PARTIAL => 'Upload Interrupted, try again',
            UPLOAD_ERR_NO_FILE => 'Upload file missing, try again',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write to disk (full?)',
            UPLOAD_ERR_EXTENSION => 'File uploasd stopped by PHP extension'
        );
        return $errors[$errorNo];
    }
    
    
    protected function getFilesWaiting()
    {
        $em = $this->getDoctrine()->getManager();
        return sizeof($em->getRepository('App:ImportedFile')->findBy(['processed'=>null]));
    }

    protected function outputXlsx($header, $data, $filename = '')
    {
        $excel = $this->get('phpexcel')->createPHPExcelObject();
        $writer = $this->get('phpexcel')->createWriter($excel, 'Excel5');
        $response = $this->get('phpexcel')->createStreamedResponse($writer);

        $sheet = $excel->setActiveSheetIndex(0);

        if ($header){
            array_unshift($data, $header);
        }

        $sheet->fromArray($data);
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            (isset($filename)?$filename:'export') . date('YmdHis') . '.xls'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

    protected function outputCsv($headers, $statement, $filename = '')
    {
        $filename = ($filename!=''?$filename:'export') . date('YmdHis') . '.csv';

        $response = new Response();
        $response->headers->set('Content-Type', "text/csv");
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$filename.'"');
        $response->headers->set('Pragma', "no-cache");
        $response->headers->set('Expires', "0");
        $response->headers->set('Content-Transfer-Encoding', "binary");

        $fp = fopen('php://temp', "w");
        fputcsv($fp, $headers);
        while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
            fputcsv($fp, $row);
        }
        rewind($fp);

        $response->setContent(stream_get_contents($fp));
        return $response;
    }

}