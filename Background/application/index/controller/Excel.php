<?php
namespace app\index\controller;

use \think\File;
use \think\Loader;

class Excel
{
    /**
     * 导出excel
     * @param array  $data 导出数据
     * @param string $savefile 导出excel文件名
     * @param array  $fileheader excel的表头
     * @param string $sheetname sheet的标题名
     */
    public function exportExcel($data, $savefile, $fileheader, $sheetname)
    {
        //引入phpexcel核心文件
        // $path = dirname(__FILE__); //找到当前脚本所在路径
        Loader::import('PHPExcel.PHPExcel'); //必须手动导入，否则会报PHPExcel类找不到
        Loader::import('PHPExcel.PHPExcel.Reader.Excel2007');
        Loader::import('PHPExcel.PHPExcel.Worksheet.Drawing');
        Loader::import('PHPExcel.PHPExcel.Writer.Excel2007');
        Loader::import('PHPExcel.PHPExcel.IOFactory.PHPExcel_IOFactory'); //引入IOFactory.php 文件里面的PHPExcel_IOFactory这个类

        //new一个PHPExcel类，或者说创建一个excel，tp中“\”不能掉
        $excel = new \PHPExcel();
        if (is_null($savefile)) {
            $savefile = time();
        } else {
            //防止中文命名，下载时ie9及其他情况下的文件名称乱码
            iconv('UTF-8', 'GB2312', $savefile);
        }
        //设置excel属性
        $objActSheet = $excel->getActiveSheet();
        //根据有生成的excel多少列，$letter长度要大于等于这个值
        $letter = array('A', 'B', 'C', 'D', 'E', 'F');
        //设置当前的sheet
        $excel->setActiveSheetIndex(0);
        //设置sheet的name
        $objActSheet->setTitle($sheetname);
        //设置表头
        for ($i = 0; $i < count($fileheader); $i++) {
            //单元宽度自适应,1.8.1版本phpexcel中文支持勉强可以，自适应后单独设置宽度无效
            $objActSheet->getColumnDimension("$letter[$i]")->setAutoSize(true);
            //设置表头值，这里的setCellValue第二个参数不能使用iconv，否则excel中显示false
            $objActSheet->setCellValue("$letter[$i]1", $fileheader[$i]);
            //设置表头字体样式
            $objActSheet->getStyle("$letter[$i]1")->getFont()->setName('微软雅黑');
            //设置表头字体大小
            $objActSheet->getStyle("$letter[$i]1")->getFont()->setSize(10);
            //设置表头字体是否加粗
            $objActSheet->getStyle("$letter[$i]1")->getFont()->setBold(false);
            //设置表头文字垂直居中
            $objActSheet->getStyle("$letter[$i]1")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            //设置文字上下居中
            $objActSheet->getStyle($letter[$i])->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            //设置表头外的文字垂直居中
            $excel->setActiveSheetIndex(0)->getStyle($letter[$i])->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        }
        // 文件重命名,避免中文乱码
        $savefile = iconv("utf-8", "gb2312", $savefile);
        $count = 2;
        foreach ($data as $k => $v) {
            $excel->setActiveSheetIndex(0)
                ->setCellValue('A' . $count, $v['user_name'])
                ->setCellValue('B' . $count, $v['user_mobile'])
                ->setCellValue('C' . $count, $v['clock_start_at_conv'])
                ->setCellValue('D' . $count, $v['clock_end_at_conv'])
                ->setCellValue('E' . $count, $v['clock_end_at'] - $v['clock_start_at'])
                ->setCellValue('F' . $count, $v['status']);
            $count++;
        }

        //清除缓冲区,避免乱码
        ob_end_clean();

        // 保存excel在服务器上
        $objWriter = new \PHPExcel_Writer_Excel2007($excel);
        $objWriter->save(ROOT_PATH . DS . 'public' . DS . 'static' . DS . "excel" . DS . $savefile . '.xlsx');

        $baseUrl = "https://test.kekexunxun.com/static";
        $downloadPath = $baseUrl . DS . "excel" . DS . $savefile . '.xlsx';
        return $downloadPath;
    }
}