<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use AppBundle\Entity\WidgetPlacement;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', array(
            'base_dir' => realpath($this->container->getParameter('kernel.root_dir').'/..').DIRECTORY_SEPARATOR,
        ));
    }


    /**
     * @Route("/widgetplacement", name="api_widgets_placements")
     */
    public function widgetplacementAction(Request $request)
    {
        $widgetRepo = $this->getDoctrine()->getRepository(WidgetPlacement::class);

        // Get params
        $maxItems   = $request->get('max_items');
        $placement  = $request->get('placement');
        $pageSize   = $request->get('page_size');
        $totalPage  = $request->get('total_page');

        $enabledWidgets = $widgetRepo->createQueryBuilder('p')
                            ->andWhere('p.enabled = :enabled')
                            ->andWhere('p.placement = :placement')
                            ->setParameter('enabled', true)
                            ->setParameter('placement', $placement)
                            ->getQuery()->getResult();
        
        $results = [];
        $densities = [];
        // Get maxItem*pageSize widgets by density
        $sumDensity = 0;
        foreach($enabledWidgets as $widget)
        {
            $tDensity = (object) [  'id'            => $widget->getId(), 
                                    'to_density'    => $sumDensity, 
                                    'from_density'  => $sumDensity+$widget->getDensity(),
                                    'selected'      => false
                                ];
            $sumDensity += $widget->getDensity();
            $densities[] = $tDensity;
        } 

        $startPlaceIndex = $maxItems * $pageSize * ($totalPage - 1);
        // Generate 4 pages
        for( $index = 0; $index < 4; $index ++)
        {
            $selectedCount = 0;
            // Generate random places
            $places = range($index*$pageSize + $startPlaceIndex, ($index+1)*$pageSize-1 + $startPlaceIndex);
            shuffle($places);

            while( $selectedCount < $maxItems ) {
                
                // Generate random value between 0, $sumDensity
                $random = rand(0, $sumDensity*10)/10;
                
                foreach($densities as $density) {
                    // Check if the generated value in the range of density
                    if(($density->to_density <= $random) && ($random < $density->from_density)) {
                        $selectedCount ++;
                        $density->selected = true;

                        // Add widget to result
                        $widget = $widgetRepo->find($density->id);
                        $tResult = (object) [   'type'  => $widget->getType(), 
                                                'place' => $places[$selectedCount],
                                                'url'   => $widget->getUrl()
                                            ];
                        $results[] = $tResult;
                    }
                }
            }
        }
        
        // Sort by place number
        usort($results, function($a, $b){
            return $a->place > $b->place;
        });

        $response = new Response();
        $response->setContent(json_encode($results));

        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Access-Control-Allow-Origin', '*');

        return $response;
    }

    function cmp($a, $b)
    {
        return ($a->place) > ($b->place);
    }

}
