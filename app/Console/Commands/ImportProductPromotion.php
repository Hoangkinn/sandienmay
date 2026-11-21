<?php

namespace App\Console\Commands;

use App\Models\New_Product;
use App\Models\Product;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ImportProductPromotion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:promotion {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
         $filePath = $this->argument('file');

        if (!file_exists($filePath)) {
            $this->error(' File không tồn tại');
            return Command::FAILURE;
        }

        DB::beginTransaction();

        try {

            Excel::import(new class implements ToCollection, WithHeadingRow {

                public function collection(Collection $rows)
                {
                    foreach ($rows as $index => $row) {

                        if (empty($row['id']) || !isset($row['khuyen_mai'])) {
                            continue;
                        }
                        $htmlPromotion = $this->promotionTextToHtmlList($row['khuyen_mai']);
                        $updated = New_Product::where('id', $row['id'])
                            ->update([
                                'promotion' => $htmlPromotion
                            ]);

                        if ($updated === 0) {
                             continue;
                        }
                    }
                }

                private function promotionTextToHtmlList(string $text): string
                {
                    $lines = preg_split("/\r\n|\n|\r/", trim($text));

                    $lis = array_filter(array_map(function ($line) {
                        $line = trim($line);
                        return $line ? '<li>' . e($line) . '</li>' : null;
                    }, $lines));

                    return '<ul>' . implode('', $lis) . '</ul>';
                }

            }, $filePath);

            DB::commit();
            $this->info('Import khuyến mãi thành công');

            return Command::SUCCESS;

        } catch (\Throwable $e) {

            DB::rollBack();
            $this->error('Import thất bại');
            $this->error($e->getMessage());

            return Command::FAILURE;
        }
    }

    

}
