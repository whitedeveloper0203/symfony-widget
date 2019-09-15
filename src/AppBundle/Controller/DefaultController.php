<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\WidgetPlacement;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $widget = new WidgetPlacement();
        $widget->setType('facebook');
        $widget->setDensity(19.9);
        $widget->setEnabled(true);
        $widget->setPlacement('Ergonomic and stylish!');
        $widget->setUrl('123');

        $entityManager = $this->getDoctrine()->getManager();

        // tells Doctrine you want to (eventually) save the Product (no queries yet)
        $entityManager->persist($widget);

        // actually executes the queries (i.e. the INSERT query)
        $entityManager->flush();

        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', array(
            'base_dir' => realpath($this->container->getParameter('kernel.root_dir').'/..').DIRECTORY_SEPARATOR,
        ));
    }


    /**
     * @Route("/widgetplacement", name="homepage_widgetplacement")
     */
    public function widgetplacementAction(Request $request)
    {
        $widgetRepo = $this->getDoctrine()->getRepository(WidgetPlacement::class);

        // Get params
        $maxItems   = $request->get('max_items');
        $placement  = $request->get('placement');
        $pageSize   = $request->get('page_size');

        $enabledWidgets = $widgetRepo->createQueryBuilder('p')
                            ->andWhere('p.enabled = :enabled')
                            ->andWhere('p.placement = :placement')
                            ->setParameter('enabled', true)
                            ->setParameter('placement', $placement)
                            ->getQuery()->getResult();
        
        $results = [];
        $densities = [];
        // Get maxItem*10 widgets by density
        $sumDensity = 0;
        foreach($enabledWidgets as $widget)
        {
            $tDensity = (object) [  'id' => $widget->getId(), 
                                    'to_density' => $sumDensity, 
                                    'from_density' => $sumDensity+$widget->getDensity(),
                                    'selected' => false
                                ];
            $sumDensity += $widget->getDensity();
            $densities[] = $tDensity;
        } 

        $selectedCount = 0;
        while( 1 ) {
            
            if($selectedCount == count($densities) || $selectedCount >= $maxItems * 10)
                break;
            
            // Generate random value between 0, $sumDensity
            $random = rand(0, $sumDensity*10)/10;
            
            foreach($densities as $density) {
                // Check if the generated value in the range of density
                if(($density->to_density <= $random) && ($random < $density->from_density) && ($density->selected == false)) {
                    $selectedCount ++;
                    $density->selected = true;

                    // Add widget to result
                    $widget = $widgetRepo->find($density->id);
                    $tResult = (object) [   'type' => $widget->getType(), 
                                            'place' => $selectedCount,
                                            'url' => $widget->getUrl()
                                        ];
                    $result[] = $tResult;
                }
            }
        }

        $response = new Response();
        $response->setContent(json_encode($result));

        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Access-Control-Allow-Origin', '*');

        return $response;
    }
}
