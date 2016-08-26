<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;

use App\Show;
use App\AnimeSentinel\ShowManager;

class ShowController extends Controller
{
  /**
   * Show the details page for a show.
   *
   * @return \Illuminate\Http\Response
   */
  public function details($show_id, $title = '') {
    if (is_numeric($show_id)) {
      $show = Show::find($show_id);
    }

    if (isset($show)) {
      if ($title === slugify($show->title)) {
        if (!visitPage('show_'.$show->id)) {
          $show->hits++;
          $show->save();
        }
        return view('anime.details', [
          'show' => $show
        ]);
      }

      elseif (empty($title)) {
        return redirect($show->details_url);
      }
      else {
        foreach ($show->alts as $alt) {
          if ($title === slugify($alt)) {
            return redirect($show->details_url);
          }
        }
      }
    }

    if (!empty($title)) {
      $title = str_replace('‑', ' ', $title);
      $show = Show::withTitle($title)->first();
      if (isset($show)) {
        return redirect($show->details_url);
      } else {
        $show = ShowManager::addShowWithTitle($title, 'default', false, false);
        if (isset($show)) {
          return redirect($show->details_url);
        }
      }
    }

    abort(404);
  }

  /**
   * Attempt to add the requested show to the database.
   *
   * @return \Illuminate\Http\Response
   */
  public function insert(Request $request) {
    $this->validate($request, [
      'mal_id' => ['required']
    ]);

    $show = ShowManager::addShowWithMalId($request->mal_id);

    if ($request->gotodetails) {
      return redirect($show->details_url);
    } else {
      return back();
    }
  }
}
