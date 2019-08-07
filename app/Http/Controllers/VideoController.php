<?php

namespace App\Http\Controllers;
use Algolia\AlgoliaSearch\Response\RestoreApiKeyResponse;
use FFMpeg;
use App\Video;
use App\Thumbnail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use FFMpeg\Format\Video\X264;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\Coordinate\Dimension;
use FFMpeg\Filters\Video\ClipFilter;
use App\Jobs\ConvertVideoForStreaming;
use Illuminate\Support\Facades\Storage;
use App\Jobs\ConvertVideoForDownloading;
class VideoController extends Controller
{
    public function index()
    {
        $videos=Video::all();
        return  response()->json($videos);
    }


    public function show(Video $video)
    {
       // $downloadUrl = Storage::disk('downloadable_videos')->url($video->id . '.mp4');
       // $str 'tag_name' =>,eamUrl = Storage::disk('streamable_videos')->url($video->id . '.m3u8');
      $tag_name=$video->tag()->pluck('name');
       // return response()->json($tag_name, 201);
        return  response()->json(array(
            'data' =>$video,
            'tag_name' => $tag_name,
        ));


    }
    public function store(Request $request)
    {
        $rules = [
            'title' => 'required',
            'tag_id' => 'required',
            'video' => 'required|file|mimetypes:video/mp4,video/mpeg,video/x-matroska',
        ];

        $this->validate($request, $rules);
      //  $data = $request->all();
        $video = Video::create([
            'disk'          => 'videos',
            'original_name' => $request->video->getClientOriginalName(),
            'path'          => $request->video->store(''),
            'title'         => $request->title,
            'tag_id'        =>$request->tag_id,
        ]);
        $this->add_duration($video);
        $this->add_frame($video);
        $this->add_thumbnails($video);
       return response()->json($video, 201);
    }
    public function add_convert_to_audio(Video $video)
    {
        if( Storage::disk('public')->get($video->title . '_' . $video->id . '.mp3'))
            return Storage::disk('public')->download($video->title.'_'.$video->id.'.mp3');
      else {
          FFMpeg::
          open($video->path)
              ->export()
              ->toDisk('public')
              ->inFormat(new \FFMpeg\Format\Audio\Mp3)
              ->save($video->title . '_' . $video->id . '.mp3');
          $video->update([
              'DownloadedAsMp3Url' => Storage::disk('public')->url($video->title . '_' . $video->id . '.mp3')
          ]);
          return Storage::disk('public')->download($video->title . '_' . $video->id . '.mp3');
      }
    }

    public function add_duration(Video $video)
    {
        $file = FFMpeg::
            open($video->path);
        $durationInSeconds = $file->getDurationInSeconds();
        $video->update([
            'duration' =>$durationInSeconds.' s',
        ]);
    }

    public  function add_frame(Video $video){
        FFMpeg::
            open($video->path)
            ->getFrameFromSeconds(10)
            ->export()
            ->toDisk('public')
            ->save($video->id.'.png');

        $video->update([
            'frame' =>Storage::disk('public')->url($video->id.'.png'),
        ]);
    }
    public function add_thumbnails(Video $video)
    {
        $cont=0;
        $file = FFMpeg::open($video->path);
        $durationInSeconds = $file->getDurationInSeconds();
        for ($x = 1; $x <=$durationInSeconds; $x=$x+20) {
            $cont++;
            $unique_name=$video->id . '-' . $cont .str_random(10).'.png';
            $file->getFrameFromSeconds($x)
                ->export()
                ->toDisk('public')
                ->save($unique_name);
            Thumbnail::create([
                'thumbnail' =>Storage::disk('public')->url($unique_name),
                'video_id' => $video->id,
            ]);
        }

    }
    public function get_thumb(Video $video){
        $thumbnails=$video->thumbnails()->where('video_id','=',$video->id)->get();
        return response()->json($thumbnails,201);
    }

    public function  download(Video $video)
    {
        //$result= $this->dispatch(new ConvertVideoForDownloading($video));
        $video->update([
            'converted_for_downloading_at' => Carbon::now(),
        ]);
    return Storage::download($video->path,$video->title.' _downloadable'.'.mp4');
       // return response()->download($video->path,$video->title.'.mp4');


    }
    public function  add_stream(Video $video)
    {
       $this->dispatch(new ConvertVideoForStreaming($video));
    }

    public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|min:3',
        ]);

        $query = $request->input('query');

        // $videos = Video::where('name', 'like', "%$query%")
          //                 ->orWhere('original_name', 'like', "%$query%");
        $videos = Video::search($query)->paginate(10);
        return response()->json($videos,201);
    }
    public function tag(Video $video)
    {
     //   $video->tag()
        response()->json($video);
    }




}
