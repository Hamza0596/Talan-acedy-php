<?php


namespace App\Service;


use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExcelToArrayService
{
    const ERROR = 'error';
    const MSG = 'msg';
    const EXCEL_DIRECTORY = 'excel_directory';

    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;

    public function __construct(ParameterBagInterface $parameterBag)
    {

        $this->parameterBag = $parameterBag;
    }
    public function excelToArray($directory, UploadedFile $file)
    {
        $file->move(
            $this->parameterBag->get(self::EXCEL_DIRECTORY),
            $file->getClientOriginalName()
        );
        $reader = IOFactory::createReader("Xlsx");
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($directory . $file->getClientOriginalName());
        return $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

    }

    public function getExcelExample($spreadsheet,$fileName)
    {
        $writer = IOFactory::createWriter($spreadsheet, "Xlsx");
        $response = new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        });
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE,
            $fileName
        );

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', $dispositionHeader);
        return $response;
    }
}
