<?php
/**
 * excel export,导出数据到excel中
 * created by caoyouming,2018-5-1
 * 导出各专业年级的选课名单，教务系统用
 */ 
function export($datas,$name,$year){
    include '/PHPExcel-1.8/Classes/PHPExcel.php';
    include '/PHPExcel-1.8/Classes/PHPExcel/Writer/Excel2007.php';
    //或者include 'PHPExcel/Writer/Excel5.php'; 用于输出.xls的
    // 创建一个excel
    $objPHPExcel = new PHPExcel();

    // Set document properties 设置文档属性
    $objPHPExcel->getProperties()->setCreator("Phpmarker")->setLastModifiedBy("Phpmarker")->setTitle("Phpmarker")->setSubject("Phpmarker")->setDescription("Phpmarker")->setKeywords("Phpmarker")->setCategory("Phpmarker");
    //表头属性
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', '学号')->setCellValue('B1', '重修标识')->setCellValue('C1', '选课课号');
    
    // 设置Excel中工作表名称，即当前工作区下方的名字
    $objPHPExcel->getActiveSheet()->setTitle($name . date('Y-m-d'));
    
    // Set active sheet index to the first sheet, so Excel opens this as the first sheet将活动表索引设置为第一个表，因此Excel将作为第一个表打开此表
    $objPHPExcel->setActiveSheetIndex(0);
    $objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(15);
    $objPHPExcel->getActiveSheet()->freezePane('A2');
    //数据
    foreach($datas as $key=>$value){
        $i = $key+2;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $value['s_num'])->getStyle('A'.$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, '');
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, '(' . $year . ')-' . $value['c_num']);
    }

    $objActSheet = $objPHPExcel->getActiveSheet();
        
    // 设置CELL填充颜色
    $cell_fill = array(
    'A1',
    'B1',
    'C1',
    );
    foreach($cell_fill as $cell_fill_val){
    $cellstyle = $objActSheet->getStyle($cell_fill_val);
    // background
    // $cellstyle->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('fafa00');
    // set align
    $cellstyle->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
    // font
    $cellstyle->getFont()->setSize(12)->setBold(true);
    // border
    $cellstyle->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN)->getColor()->setARGB('FFFF0000');
    $cellstyle->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN)->getColor()->setARGB('FFFF0000');
    $cellstyle->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN)->getColor()->setARGB('FFFF0000');
    $cellstyle->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN)->getColor()->setARGB('FFFF0000');
    }

    $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(30);
    
    $objActSheet->getColumnDimension('A')->setWidth(18.5);
    $objActSheet->getColumnDimension('B')->setWidth(18.5);
    $objActSheet->getColumnDimension('C')->setWidth(30);


    $filename = "$name";
    ob_end_clean();//清除缓冲区,避免乱码 
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
    header('Cache-Control: max-age=0');
    // If you're serving to IE 9, then the following may be needed
    header('Cache-Control: max-age=1');

    // If you're serving to IE over SSL, then the following may be needed
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
    header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
    header('Pragma: public'); // HTTP/1.0

    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
    $objWriter->save('php://output');
 }
?>