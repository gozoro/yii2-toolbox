<?php

namespace gozoro\toolbox\phpoffice\phpexcel;


/**
 * Abstract Excel report class.
 * Report helper.
 */
abstract class ExcelReport
{
	/**
	 * Excel
	 * @var \PHPExcel
	 */
	protected $xls;

	public function __construct($creator = "PHP", $fontSize = 10, $fontName = 'Arial')
	{
		$this->xls = new \PHPExcel();
		$this->xls->getProperties()->setCreator($creator);
		$this->xls->getDefaultStyle()->getFont()->setName($fontName);
		$this->xls->getDefaultStyle()->getFont()->setSize($fontSize);
	}

	/**
	 * Enable/Disable style font BOLD.
	 * @return array
	 */
	protected function styleBold($bold=true)
	{
		return array('font'=>array('bold'=>(bool)$bold));
	}

	/**
	 * Enable/Disable word wrap.
	 * @param bool $wrap
	 * @return array
	 */
	protected function styleWrap($wrap=true)
	{
		return array('alignment' => array('wrap'=>(bool)$wrap));
	}

	/**
	 * Sets the font size.
	 * @param int $size
	 * @return array
	 */
	protected function styleFontSize($size)
	{
		return array('font'=>array('size'=>(int)$size));
	}

	/**
	 * Sets the font color.
	 * @param string $color RGB-color
	 * @return array
	 */
	protected function styleColor($color='000000')
	{
		return array('font' => array('color' => array('rgb' => $color) ) );
	}

	/**
	 * Sets the background of cells.
	 * @param string $color RGB-color
	 * @return array
	 */
	protected function styleBackground($color='FFFFFF')
	{
		return array('fill' => array('type' => \PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => $color) ) );
	}


	/**
	 * Draws a grid around each cell.
	 * @param string $background the background color (e.g. "f2aa06")
	 * @return array
	 */
	protected function styleTableGrid($background = null)
	{
		$style = array(
			'borders' => array('allborders' => array('style' => \PHPExcel_Style_Border::BORDER_THIN)),
		);

		if($background)
			$style['fill'] =  array('type' => \PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => $background) );

		return $style;
	}



	/**
	 * Cells in the frame (frame sets around the perimeter of the range).
	 * @param string $background the background color (e.g. "f2aa06")
	 * @return array
	 */
	protected function styleFrame($background = null)
	{
		$style = array(
			'borders' => array(
				'top' => array('style' => \PHPExcel_Style_Border::BORDER_THIN),
				'bottom' => array('style' => \PHPExcel_Style_Border::BORDER_THIN),
				'left' => array('style' => \PHPExcel_Style_Border::BORDER_THIN),
				'right' => array('style' => \PHPExcel_Style_Border::BORDER_THIN)
				),
			'alignment' => array('wrap' => true,
								'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
								'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER,
								),
		);


		if($background)
			$style['fill'] =  array('type' => \PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => $background) );


		return $style;
	}

	/**
	 * Sets horizontal alignment: LEFT.
	 * @return array
	 */
	protected function styleAlignLeft()
	{
		return array('alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_LEFT));
	}

	/**
	 * Sets horizontal alignment: RIGHT.
	 * @return array
	 */
	protected function styleAlignRight()
	{
		return array('alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_RIGHT));
	}

	/**
	 * Sets horizontal alignment: JUSTIFY.
	 * @return array
	 */
	protected function styleAlignJustify()
	{
		return array('alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_JUSTIFY));
	}

	/**
	 * Sets horizontal alignment: CENTER.
	 * @param bool $andVerticalCenter  Sets vertical alignment: CENTER.
	 * @return array
	 */
	protected function styleAlignCenter($andVerticalCenter=false)
	{
		if($andVerticalCenter)
			return array('alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER, 'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER));
		else
			return array('alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
	}

	/**
	 * Sets vertical alignment: TOP.
	 * @return array
	 */
	protected function styleAlignTop()
	{
		return array('alignment' => array('vertical' => \PHPExcel_Style_Alignment::VERTICAL_TOP));
	}

	/**
	 * Sets vertical alignment: BOTTOM.
	 * @return array
	 */
	protected function styleAlignBottom()
	{
		return array('alignment' => array('vertical' => \PHPExcel_Style_Alignment::VERTICAL_BOTTOM));
	}

	/**
	 * Sets rotation angle.
	 * @param int $angle
	 * @return array
	 */
	protected function styleRotation($angle)
	{
		return array('alignment'=>array('rotation'=>(int)$angle));
	}

	/**
	 * Sets rotation angle: 90.
	 * @return array
	 */
	protected function styleRotation90()
	{
		return $this->styleRotation(90);
	}

	/**
	 * Sets sum format.
	 * @param string $format excel number format
	 * @return type
	 */
	protected function styleSum($format = '# ### ### ##0.00')
	{
		return array(
	              'alignment' => array('wrap'=>true,'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_RIGHT, 'vertical'=>\PHPExcel_Style_Alignment::VERTICAL_CENTER),
	              'numberformat' => array('code' => $format)
			);
	}

	/**
	 * Returns an empty string with a zero sum.
	 *
	 * @param float $sum
	 * @param int $precision sum rounding precision
	 * @return string
	 */
	protected function sum($sum, $precision = 2)
	{
		$sum = round($sum, $precision);

		if($sum == 0)
			return '';
		else
			return $sum;
	}

	/**
	 * Returns an empty string with a zero number.
	 *
	 * @param int $number
	 * @return string
	 */
	protected function number($number)
	{
		$number = (int)$number;

		if($number == 0)
			return '';
		else
			return $number;
	}

	/**
	 * Saves report to CSV-file.
	 * @param string $filename
	 * @return bool
	 */
	protected function saveCSV($filename)
	{
		$excelWriter = new \PHPExcel_Writer_CSV($this->xls);
		$excelWriter->save($filename);
		$this->xls->disconnectWorksheets();
		return true;
	}

	/**
	 * Saves report to XLSX-file.
	 * @param string $filename
	 * @return bool
	 */
	protected function saveXLSX($filename)
	{
		$excelWriter = new \PHPExcel_Writer_Excel2007($this->xls);
		$excelWriter->save($filename);
		$this->xls->disconnectWorksheets();
		return true;
	}

	/**
	 * Saves report to XLS-file.
	 * @param string $filename
	 * @return bool
	 */
	protected function saveXLS($filename)
	{
		$excelWriter = new \PHPExcel_Writer_Excel5($this->xls);
		$excelWriter->save($filename);
		$this->xls->disconnectWorksheets();
		return true;
	}


	/**
	 * Saves report to file (csv, xls or xlsx).
	 * @param string $filename
	 * @return bool
	 */
	public function save($filename)
	{
		$parts = explode('.', $filename);
		$ext = strtolower($parts[ count($parts)-1 ]);

		switch($ext)
		{
			case 'csv':  return $this->saveCSV($filename);
			case 'xls':  return $this->saveXLS($filename);
			case 'xlsx': return $this->saveXLSX($filename);
			default:     return $this->saveXLSX($filename);
		}
	}


	/**
	 * Download the report to php://output.
	 * The method sets the appropriate http headers.
	 * @param string $filename file name for the user
	 */
	public function download($filename = 'report.xlsx')
	{
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.$filename.'"');
		header('Cache-Control: max-age=0');

		$this->save('php://output');
	}

}