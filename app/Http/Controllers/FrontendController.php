<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\InfoGraphic;
use App\Models\Product;
use App\Models\Sector;
use App\Models\SubSector;
use Illuminate\Http\Request;

class FrontendController extends Controller
{
    public function index()
    {
        $allSector = Sector::all();
        $allblog = Blog::latest()->take(2)->get();
        $dataset = Product::where('dataset', 1)->latest()->take(2)->get();
        $events = Event::latest()->take(2)->get();
        $infoGraphics = InfoGraphic::latest()->take(2)->get();
        return view('ui.index', compact('allSector', 'allblog', 'dataset', 'events', 'infoGraphics'));
    }

    // public function Sector()
    // {
    //     $allSector = Sector::all();
    //     return view('frontend.sector', compact('allSector'));
    // }
    // public function SectorEdit($id)
    // {
    //     $editSector = Sector::find($id);
    //     return view('frontend.subsector', compact('editSector'));
    // }

    // public function subsector($id)
    // {
    //     $sector = Sector::find($id);
    //     if (!$sector) {
    //         return redirect()->back();
    //     }
    //     $subSectors = SubSector::where('sector_id', $id)->get();
    //     $products = Product::where('subsector_id', $id)->get();
    //     return view('frontend.subsector', compact('subSectors', 'sector', 'products'));
    // }

    // public function viewSubsector($id)
    // {
    //     $subSector = SubSector::find($id);
    //     if (!$subSector) {
    //         return redirect()->back();
    //     }
    //     $products = Product::where('subsector_id', $id)->get();
    //     $sector = $subSector->sector;
    //     $subSectors = SubSector::where('sector_id', $sector->id)->get();
    //     return view('frontend.subsector', compact('subSectors', 'sector', 'products'));
    // }
    // public function blogCategory()
    // {
    //     $allblogCategory = BlogCategory::all();
    //     return view('frontend.blog.blogcategory', compact('allblogCategory'));
    // }
    // public function view($id)
    // {
    //     $viewblog = BlogCategory::find($id);
    //     $allblog = Blog::where('category_id', $id)->get();
    //     return view('frontend.blog.blog', compact('viewblog', 'allblog'));
    // }
    // public function Readmore($id)
    // {
    //     $view = Blog::find($id);
    //     return view('frontend.blog.readmore', compact('view'));
    // }

    // public function dataset($id)
    // {
    //     Product::where('sector_id', $id)->where('dataset', 1)->increment('views');
    //     $sectors = Sector::all();
    //     $product = Product::where('sector_id', $id)->where('dataset', 1)->get();
    //     return view('frontend.highvalue', compact('product', 'sectors'));
    // }
    // public function datasetView()
    // {
    //     $product = Product::where('dataset', 1)->orderBy('views', 'desc')->get();
    //     return view('frontend.viewHighvalue', compact('product'));
    // }
    // public function recentlyAdded()
    // {
    //     $products = Product::where('dataset', 1)->orderBy('created_at', 'desc')->get();
    //     return view('frontend.recentaddHighvalue', compact('products'));
    // }
    // public function eventCategory()
    // {
    //     $eventCategory = EventCategory::all();
    //     return view('frontend.event.eventcategory', compact('eventCategory'));
    // }
    // public function eventView($id)
    // {
    //     $viewEventcategory = EventCategory::find($id);
    //     $allevent = Event::where('category_id', $id)->get();
    //     return view('frontend.event.event', compact('viewEventcategory', 'allevent'));
    // }
    // public function eventReadmore($id)
    // {
    //     $viewevent = Event::find($id);
    //     return view('frontend.event.readmore', compact('viewevent'));
    // }
    // public function infographics()
    // {
    //     return view('frontend.infographics');
    // }
}
