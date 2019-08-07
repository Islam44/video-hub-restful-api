<?php

namespace App\Jobs;

use FFMpeg;
use App\Video;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use FFMpeg\Format\Video\X264;;
use FFMpeg\Coordinate\Dimension;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;

class ConvertVideoForStreaming implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $video;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Video $video)
    {
        $this->video = $video;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //create a video format...
      //  $lowBitrateFormat = (new X264)->setKiloBitrate(250);
      //  $midBitrate = (new X264)->setKiloBitrate(500);
      //  $highBitrate = (new X264)->setKiloBitrate(1000);

        // create a video format...
        $lowBitrateFormat = (new X264('libmp3lame', 'libx264'))->setKiloBitrate(250);
        $midBitratelowBitrateFormat = (new X264('libmp3lame', 'libx264'))->setKiloBitrate(500);
        $highBitrateFormat = (new X264('libmp3lame', 'libx264'))->setKiloBitrate(1000);


        $converted_name = 'Stream_ '.$this->getCleanFileName($this->video->path);

        // open the uploaded video from the right disk...
        \Pbmedia\LaravelFFMpeg\FFMpegFacade::
        open($this->video->path)

            // add the 'resize' filter...
            ->addFilter(function ($filters) {
                $filters->resize(new Dimension(960, 540));
            })

            // call the 'export' method...
            ->export()

            // tell the MediaExporter to which disk and in which format we want to export...
            ->toDisk('public')
            ->inFormat($lowBitrateFormat)

            // call the 'save' method with a filename...
            ->save($converted_name);

        // update the database so we know the convertion is done!
        $this->video->update([
            'converted_for_streaming_at' => Carbon::now(),
            'processed' => true,
            'stream_path' => Storage::disk('public')->url($converted_name)
        ]);
        return response()->json($this->video,201);
    }

    private function getCleanFileName($filename){
        return preg_replace('/\\.[^.\\s]{3,4}$/', '', $filename) . '.mp4';
    }

}
