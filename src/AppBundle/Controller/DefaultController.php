<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Visitor;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\DateTime;
use JMS\DiExtraBundle\Annotation as DI;

class DefaultController extends Controller
{

    /**
     * @var \Doctrine\ORM\EntityManager
     * @DI\Inject("doctrine.orm.entity_manager")
     */
    protected $em;


    /**
     * @Route("/", name="homepage")
     * @Template()
     * @param Request $request
     * @return array
     */
    public function indexAction(Request $request)
    {
        return $this->getVisitor($request);
    }

    /**
     * @Route(
     *     "/index1.{_format}",
     *      name="first-page",
     *     requirements={
     *         "_format": "html"
     *     }
     * )
     * @Template()
     * @param Request $request
     * @return array
     */
    public function index1Action(Request $request)
    {

        return $this->getVisitor($request);
    }

    /**
     * @Route(
     *     "/index2.{_format}",
     *      name="second-page",
     *     requirements={
     *         "_format": "html"
     *     }
     * )
     * @Template()
     * @param Request $request
     * @return array
     */
    public function index2Action(Request $request)
    {
        return $this->getVisitor($request);
    }


    /**
     * @Route("/addVisitor", name="add-visitor")
     * @param Request $request
     * @return JsonResponse
     */
    public function addVisitorAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $data = $request->query->get('data');
            $array = (array) json_decode($data);
            $id = $array['id'];
            $ip = (string) $array['ip'];
            $agent = (string) $array['agent'];
            $url = (string) $array['url'];
            $count = (int) $array['count'];
            if (null === $id) {
                $repository = $this->em->getRepository(Visitor::class);
                $visitor = $repository->findOneBy([
                    'ip' => $ip,
                    'url' => $url,
                    'agent' => $agent
                ]);
                if (!$visitor) {
                    $visitor = new Visitor();
                    $visitor->setIp($ip);
                    $visitor->setUrl($url);
                    $visitor->setAgent($agent);
                    $visitor->setCount($count);
                    $visitor->setDate(new \DateTime('now'));
                    $this->em->persist($visitor);
                    $this->em->flush($visitor);
                } else {
                    $cnt = $visitor->getCount() +1;
                    $visitor->setCount($cnt);
                    $visitor->setDate(new \DateTime('now'));
                    $this->em->persist($visitor);
                    $this->em->flush($visitor);
                }
            } else {
                $visitor = $this->em->getRepository(Visitor::class)->find((int) $id);
                $visitor->setCount($count);
                $visitor->setDate(new \DateTime('now'));
                $this->em->persist($visitor);
                $this->em->flush($visitor);
            }
            $count = $visitor->getCount();
            return new JsonResponse(['message' => 'add visitor', 'data' => $count]);
        } else {
            return new JsonResponse(['response' => 'not XmlHttp']);
        }
    }

    /**
     * @Route(
     *     "/banner.{_format}",
     *      name="banner",
     *     requirements={
     *         "_format": "php"
     *     }
     * )
     * @param Request $request
     * @return Response
     */
    public function bannerAction(Request $request)
    {
        $path = $this->container->getParameter('kernel.root_dir') . '/../web/bundles/app/images/arica.jpg';

        $file =    readfile($path);
        $headers = array(
            'Content-Type'     => 'image/png',
            'Content-Disposition' => 'inline; filename="'.$file.'"');
        return new Response($file, 200, $headers);
    }

    protected function getRealUserIp()
    {
        switch (true) {
            case (!empty($_SERVER['HTTP_X_REAL_IP'])):
                return $_SERVER['HTTP_X_REAL_IP'];
            case (!empty($_SERVER['HTTP_CLIENT_IP'])):
                return $_SERVER['HTTP_CLIENT_IP'];
            case (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])):
                return $_SERVER['HTTP_X_FORWARDED_FOR'];
            default:
                return $_SERVER['REMOTE_ADDR'];
        }
    }

    /**
     * @param Request $request
     * @return array
     */
    protected function getVisitor(Request $request)
    {
        $ua = (string) $request->headers->get('User-Agent');
        $url = (string) $request->getUri();
        $ip = (string) $this->getRealUserIp();
        /** @var EntityManager $em */
        $em = $this->em;
        $visitor = null;
        try {
            $repository = $em->getRepository(Visitor::class);
            $visitor = $repository->findOneBy([
                'ip' => $ip,
                'url' => $url,
                'agent' => $ua
            ]);
        } catch (\Exception $e) {
            die($e->getMessage());
        }
        $visitor->setDate(new \DateTime('now'));
        if (!$visitor) {
            $array = [
                'ip' => $ip,
                'url' => $url,
                'agent' => $ua,
                'count' => 1,
                'id' => null
            ];
            $visitor = new Visitor();
            $visitor->setUrl($url);
            $visitor->setAgent($ua);
            $visitor->setDate(new \DateTime('now'));
            $visitor->setCount(1);
            $visitor->setIp($ip);
        } else {
            $array = [
                'id' => $visitor->getId(),
                'ip' => $visitor->getIp(),
                'agent' => $visitor->getAgent(),
                'url' => $visitor->getUrl(),
                'count' => $visitor->getCount() +1
            ];
        }



        return [
            'visitor' => $visitor,
            'serialized_visitor' => json_encode($array)
        ];
    }
}
