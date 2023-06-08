<?php

namespace App\Services;

use App\Exceptions\RpaException;
use App\Models\Rpa;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\LocalFileDetector;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverSelect;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Rap2hpoutre\FastExcel\FastExcel;
use Smalot\PdfParser\Parser;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class RpaService
{
    private const SERVER_URL = 'http://bionexo-rpa-selenium:4444/wd/hub';

    private $webDriver;

    public function __construct() {
        $this->webDriver = RemoteWebDriver::create(self::SERVER_URL, DesiredCapabilities::chrome());
    }

    public function executeTasks(): Response {
        try {
            $successTasksNumber = array();

            $this->taskOne();
            $successTasksNumber[] = 1;

            $this->taskTwo();
            $successTasksNumber[] = 2;

            $this->taskThree();
            $successTasksNumber[] = 3;

            $this->taskFour();
            $successTasksNumber[] = 4;

            $this->taskFive();
            $successTasksNumber[] = 5;

            $sucessMsg = collect($successTasksNumber)
                ->map(fn ($taskNumber) => "Tarefa $taskNumber executada com sucesso!")
                ->join("<br />");

            return response($sucessMsg, HttpResponse::HTTP_OK);
        } catch(RpaException $rpaException) {
            Log::error($rpaException->getMessage(), ['exception' => $rpaException]);
            return response($rpaException->getMessage(), HttpResponse::HTTP_BAD_REQUEST);
        } catch(\Exception $exception) {
            Log::error($exception->getMessage(), ['exception' => $exception]);
            return response('Um erro ocorreu ao executar as tasks.', HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function taskOne() {
        try {
            $data = $this->readRpaData();

            $this->insertRpaData($data);
        } catch (RpaException $e) {
            throw new RpaException('Falha ao executar Tarefa 1: ' . $e->getMessage());
        }
    }

    public function taskTwo() {
        try {
            $this->fillRpaForm();
        } catch (RpaException $e) {
            throw new RpaException('Falha ao executar Tarefa 2: ' . $e->getMessage());
        }
    }

    public function taskThree() {
        try {
            $downloadLink = $this->getRpaDownloadFileLink();

            $this->saveRpaFile($downloadLink);
        } catch (RpaException $e) {
            throw new RpaException('Falha ao executar Tarefa 3: ' . $e->getMessage());
        }
    }

    public function taskFour() {
        try {
            $this->uploadRpaDownloadFile();
        } catch (RpaException $e) {
            throw new RpaException('Falha ao executar Tarefa 4: ' . $e->getMessage());
        }
    }

    public function taskFive() {
        try {
            $data = $this->readRpaPDFFile();

            $this->storeRpaPDFFileDataInExcel($data);
        } catch (RpaException $e) {
            throw new RpaException('Falha ao executar Tarefa 5: ' . $e->getMessage());
        }
    }

    private function readRpaData(): Collection {
        try {
            $this->webDriver->get(env('RPA_READ_URL'));

            $table = $this->webDriver->findElement(WebDriverBy::id('mytable'));

            $rows = collect($table->findElements(WebDriverBy::tagName('tr')))
                ->skip(1); // remove the table's header row
            $rpaData = [];
            foreach($rows as $row) {
                // Get all the columns within the current row
                $columns = $row->findElements(WebDriverBy::tagName('td'));

                $rpaData[] = [
                    'name' => $columns[0]?->getText(),
                    'amount' => $columns[1]?->getText(),
                ];
            }

            return collect($rpaData)
                ->filter(fn ($data) => !empty($data['name']) && !empty($data['amount']));
        } catch(\Exception) {
            throw new RpaException('Rpa não conseguiu ler tabela do site: ' . env('RPA_READ_URL'));
        }
    }

    private function insertRpaData(Collection $data): void {
        try {
            foreach($data as $rpaData) {
                Rpa::create($rpaData);
            }
        } catch(\Exception $exception) {
            throw new RpaException('Rpa não inserir dados no banco de dados: ' . $exception->getMessage());
        }
    }

    public function fillRpaForm(): void {
        try {
            $this->webDriver->get(env('RPA_FORM_URL'));

            $filePath = Storage::disk('local')->path('teste.txt');

            $this->webDriver->findElement(WebDriverBy::name('username'))->sendKeys('rcarlos');
            $this->webDriver->findElement(WebDriverBy::name('password'))->sendKeys('12345');
            $this->webDriver->findElement(WebDriverBy::name('comments'))
                ->sendKeys('testando o preenchimento do form.');
            $this->webDriver->findElement(WebDriverBy::name('filename'))
                ->setFileDetector(new LocalFileDetector())
                ->sendKeys($filePath);
            $this->webDriver->findElement(WebDriverBy::cssSelector('input[type="checkbox"][value="cb1"]'))
                ->click();
            $this->webDriver->findElement(WebDriverBy::cssSelector('input[type="checkbox"][value="cb3"]'))
                ->click();
            $this->webDriver->findElement(WebDriverBy::cssSelector('input[type="radio"][value="rd1"]'))
                ->click();
            $this->webDriver->findElement(WebDriverBy::cssSelector('input[type="radio"][value="rd2"]'))
                ->click();
            $dropdownSelect = $this->webDriver->findElement(WebDriverBy::name('dropdown'));
            $dropdownSelect = new WebDriverSelect($dropdownSelect);
            $dropdownSelect->selectByValue('dd5');
            $this->webDriver->findElement(WebDriverBy::id('HTMLFormElements'))->submit();

        } catch(\Exception $e) {
            throw new RpaException('Rpa não conseguiu preencher o formulário do site: ' . env('RPA_FORM_URL'));
        }
    }

    public function getRpaDownloadFileLink(): string {
        try {
            $this->webDriver->get(env('RPA_DOWNLOAD_URL'));

            $directLinkDownloadButton = $this->webDriver->findElement(WebDriverBy::id('direct-download-a'));

            return $directLinkDownloadButton->getDomProperty('href');
        } catch(\Exception $e) {
            throw new RpaException("Rpa não conseguiu fazer download do arquivo do site: "
                . env('RPA_DOWNLOAD_URL'));
        }
    }

    public function saveRpaFile($downloadLink): void {
        try {
            $fileContents = file_get_contents($downloadLink);
            Storage::disk('local')->put('Teste TKS.txt', $fileContents);
        } catch(\Exception $e) {
            throw new RpaException('Rpa não conseguiu fazer salvar o arquivo: ' . $downloadLink);
        }
    }

    public function uploadRpaDownloadFile(): void {
        try {
            $this->webDriver->get(env('RPA_UPLOAD_URL'));

            $filePath = Storage::disk('local')->path('Teste TKS.txt');

            $this->webDriver->findElement(WebDriverBy::id('fileinput'))
                ->setFileDetector(new LocalFileDetector())
                ->sendKeys($filePath);
            $this->webDriver->findElement(WebDriverBy::id('itsafile'))
                ->click();

            $this->webDriver->findElement(WebDriverBy::tagName('form'))->submit();
        } catch(\Exception $e) {
            throw new RpaException("Rpa não conseguiu fazer upload do arquivo $filePath no site: " . env('RPA_UPLOAD_URL'));
        }
    }

    public function readRpaPDFFile(): Collection {
        try {
            $this->convertPDFtoHTML();
            $this->webDriver->get(env('RPA_WEB_URL') . '/Leitura_PDF.html');

            $pdfExtractedData = [];
            $pdfExtractedDataUnique = [
                'Registro ANS' => $this->getTextFromContent('#page1-div p:nth-child(23)'),
                'Nome da Operadora' => $this->getTextFromContent('#page1-div p:nth-child(25)'),
                'Código na Operadora' => $this->getTextFromContent('#page1-div p:nth-child(9)'),
                'Nome do Contratado' => $this->getTextFromContent('#page1-div p:nth-child(11)'),
                'Número do Lote' => $this->getTextFromContent('#page1-div p:nth-child(14)'),
                'Número do Protocolo' => $this->getTextFromContent('#page1-div p:nth-child(16)'),
                'Data do Protocolo' => $this->getTextFromContent('#page1-div p:nth-child(18)'),
                'Código da Glosa do Protocolo' => null,

                'Valor Informado do Protocolo' => $this->getTextFromContent('#page1-div p:nth-child(53)'),
                'Valor Processado do Protocolo' => $this->getTextFromContent('#page1-div p:nth-child(55)'),
                'Valor Liberado do Protocolo' => $this->getTextFromContent('#page1-div p:nth-child(56)'),
                'Valor Glosa do Protocolo' => $this->getTextFromContent('#page1-div p:nth-child(57)'),
                'Valor Informado Geral' => $this->getTextFromContent('#page1-div p:nth-child(62)'),
                'Valor Processado Geral' => $this->getTextFromContent('#page1-div p:nth-child(64)'),
                'Valor Liberado Geral' => $this->getTextFromContent('#page1-div p:nth-child(65)'),
                'Valor Glosa Geral' => $this->getTextFromContent('#page1-div p:nth-child(66)')
            ];

            $divCounter = 1;
            $pageDivs = $this->webDriver->findElements(WebDriverBy::cssSelector('div[id^="page"][id$="-div"]'));
            foreach($pageDivs as $pageDiv) {
                if ($divCounter++ <= 2) {
                    continue;
                }

                $elementId = $pageDiv->getAttribute('id');
                $pdfExtractedData[] = [
                    ... $pdfExtractedDataUnique,
                    'Número da Guia no Prestador' => $this->getTextFromContent("#$elementId p:nth-child(10)"),
                    'Número da Guia Atribuído pela Operadora' => $this->getTextFromContent("#$elementId p:nth-child(11)"),
                    'Senha' => null,
                    'Nome do Beneficiário' => $this->getTextFromContent("#$elementId p:nth-child(15)"),
                    'Número da Carteira' => $this->getTextFromContent("#$elementId p:nth-child(17)"),
                    'Data Inicio do faturamento' => $this->getTextFromContent("#$elementId p:nth-child(19)"),
                    'Hora Inicio do Faturamento' => $this->getTextFromContent("#$elementId p:nth-child(23)"),
                    'Data Fim do Faturamento' => $this->getTextFromContent("#$elementId p:nth-child(21)"),
                    'Código da Glosa da Guia' => null,
                    'Data de realização' => null,
                    'Tabela' => null,


                    'Código do Procedimento' => optional(explode('&#160;', $this->getTextFromContent("#$elementId p:nth-child(30)")))[0],
                    'Descrição' => optional(explode('&#160;', $this->getTextFromContent("#$elementId p:nth-child(30)")))[1],
                    'Grau Participação' => null,
                    'Valor Informado' => $this->getTextFromContent("#$elementId p:nth-child(31)"),
                    'Quanti. Executada' => $this->getTextFromContent("#$elementId p:nth-child(32)"),
                    'Valor Processado' => $this->getTextFromContent("#$elementId p:nth-child(33)"),
                    'Valor Liberado' => $this->getTextFromContent("#$elementId p:nth-child(34)"),
                    'Valor Glosa' => $this->getTextFromContent("#$elementId p:nth-child(35)"),
                    'Código da Glosa' => $this->getTextFromContent("#$elementId p:nth-child(54)"),


                    'Valor Informado da Guia' => $this->getTextFromContent("#$elementId p:nth-child(53)"),
                    'Valor Processado da Guia' => $this->getTextFromContent("#$elementId p:nth-child(46)"),
                    'Valor Liberado da Guia' => $this->getTextFromContent("#$elementId p:nth-child(47)"),
                    'Valor Glosa da Guia' => $this->getTextFromContent("#$elementId p:nth-child(48)"),
                ];
            }

            return collect($pdfExtractedData);
        } catch(\Exception $e) {
            throw new RpaException("Rpa não conseguiu fazer upload do arquivo $filePath no site: " . env('RPA_UPLOAD_URL'));
        }
    }

    private function convertPDFtoHTML(): void {
        try {
            $pdfFilePath = Storage::disk('local')->path('Leitura_PDF.pdf');
            $htmlFilePath = public_path('Leitura_PDF.html');

            shell_exec("pdftohtml $pdfFilePath -s -i -noframes $htmlFilePath");
        } catch(\Exception) {
            throw new RpaException('Rpa não conseguiu converter PDF para HTML');
        }
    }

    private function getTextFromContent($cssSelector): ?string {
        try {
            return $this->webDriver->findElement(WebDriverBy::cssSelector($cssSelector))
                ->getText();
        } catch(\Exception $e) {
            return null;
        }
    }

    public function storeRpaPDFFileDataInExcel(Collection $data): void {
        if ($data->isEmpty()) {
            throw new RpaException('Não foi possível gerar relatório porque os dados estão vazios.');
        }

        (new FastExcel($data))->export(storage_path('app/Leitura_PDF.xlsx'));
    }
}
