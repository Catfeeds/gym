<?php
namespace app\index\controller;

use app\index\model\Classes;
use app\index\model\Classes_user;
use app\index\model\Teacher;
use app\index\model\User;
use \think\File;
use \think\Loader;
use \think\Session;

class Excel
{
    // public function test()
    // {
    //     //获取你要导出的数据，你要获取的到数据库的数据
    //     // $data = M("user")->field("id,username,password,img")->order("id DESC")->limit(520)->select();\
    //     $data[] = array(
    //         'order_id'   => '2',
    //         'order_sn'   => '200',
    //         'username'   => 'test',
    //         'total_fee'  => '100',
    //         'phone'      => '110',
    //         'address'    => 'china',
    //         'message'    => 'test666',
    //         'pay_at'     => '1',
    //         'created_at' => '1',
    //         'finish_at'  => '1',
    //         'cancel_at'  => '1',
    //         'status'     => '1',
    //         'goods_name' => 'test777',
    //         'quantity'   => '99',
    //         'fee'        => '100',
    //         'mch_name'   => 'market',
    //         // 'fee'=>'100',
    //     );
    //     //设置要导出excel的表头
    //     $fileheader = array('ID', '订单编号', '用户名称', '总费用', '电话号码', '地址', '用户留言', '付款时间', '创建时间', '结束时间', '取消时间', '状态', '商品名称', '商品数量', '商品单价', '店铺名称');
    //     // 输出的excel名称
    //     $excelName = date("y-m-d H:i", time());
    //     $path      = $this->exportExcel($data, $excelName, $fileheader, 'Sheet1');
    //     return $path;
    // }

    /**
     * 输入的excel模板
     * @param  int      $type  1为用户模板 2为教师模板
     * @return string          文件路径
     */
    public function template($type)
    {
        // 类型1为用户模板
        if ($type == 1) {
            //设置要导出excel的表头
            $fileheader = array(
                '用户名',
                '学号',
                '年级',
                '班级',
                '性别',
                '生日',
                '手机号',
                '剩余课时',
                '课程结束时间',
            );
            // 模板名称
            $templateName = date("y-m-d", time()) . '-student';
            // 数据
            $data = '';
            // 下拉框 第三列与第四列
            $select = 'C';
            // $select = 'D';
        }
        // 类型2为教师模板
        if ($type == 2) {
            //设置要导出excel的表头
            $fileheader = array(
                '教师名称',
                '性别',
                '生日',
                '手机号',
            );
            // 模板名称
            $templateName = date("y-m-d", time()) . '-teacher';
            // 数据
            $data = '';
            // 下拉框 第二列
            $select = 'B';
        }
        $path = $this->exportExcel($data, $templateName, $fileheader, 'Sheet1', $select, $type);
        return $path;
    }

    /**
     * 导出excel
     * @param array  $data 导出数据
     * @param string $savefile 导出excel文件名
     * @param array  $fileheader excel的表头
     * @param string $sheetname sheet的标题名
     * @param string $isselect  是否带下拉框
     * @param int    $type      模板类型
     */
    public function exportExcel($data, $savefile, $fileheader, $sheetname, $isselect = null, $type = null)
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
        if ($data != '') {
            //根据有生成的excel多少列，$letter长度要大于等于这个值
            $letter = array('A', 'B', 'C', 'D', 'E', 'F', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P');
        } else {
            if ($type == 1) {
                //根据有生成的excel多少列，$letter长度要大于等于这个值
                $letter = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I');
            }
            if ($type == 2) {
                //根据有生成的excel多少列，$letter长度要大于等于这个值
                $letter = array('A', 'B', 'C', 'D', 'E');
            }
        }
        //设置当前的sheet
        $excel->setActiveSheetIndex(0);
        //设置sheet的name
        $objActSheet->setTitle($sheetname);
        //设置表头
        for ($i = 0; $i < count($fileheader); $i++) {
            //单元宽度自适应,1.8.1版本phpexcel中文支持勉强可以，自适应后单独设置宽度无效
            //$objActSheet->getColumnDimension("$letter[$i]")->setAutoSize(true);
            //设置表头值，这里的setCellValue第二个参数不能使用iconv，否则excel中显示false
            $objActSheet->setCellValue("$letter[$i]1", $fileheader[$i]);
            //设置表头字体样式
            $objActSheet->getStyle("$letter[$i]1")->getFont()->setName('微软雅黑');
            //设置表头字体大小
            $objActSheet->getStyle("$letter[$i]1")->getFont()->setSize(12);
            //设置表头字体是否加粗
            $objActSheet->getStyle("$letter[$i]1")->getFont()->setBold(true);
            //设置表头文字垂直居中
            $objActSheet->getStyle("$letter[$i]1")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            //设置文字上下居中
            $objActSheet->getStyle($letter[$i])->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            //设置表头外的文字垂直居中
            $excel->setActiveSheetIndex(0)->getStyle($letter[$i])->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            /*设置金额字段格式为2位小数点*/
            // $excel->getActiveSheet()->getStyle('C')->getNumberFormat()->setFormatCode("0.00");
            // $excel->getActiveSheet()->getStyle('D')->getNumberFormat()->setFormatCode("0.00");
            // $excel->getActiveSheet()->getStyle('E')->getNumberFormat()->setFormatCode("0.00");
            if ($type == 1) {
                // 设置日期格式为文本
                $excel->getActiveSheet()->getStyle('E')->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                // 设置课程结束时间为文本
                $excel->getActiveSheet()->getStyle('I')->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);
            }
            if ($type == 2) {
                // 设置日期格式为文本
                $excel->getActiveSheet()->getStyle('C')->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);
            }
        }
        if ($data != '') {
            //这里$i初始值设置为2，$j初始值设置为0
            for ($i = 2; $i <= count($data) + 1; $i++) {
                $j = 0;
                foreach ($data[$i - 2] as $key => $value) {
                    //不是图片时将数据加入到excel，这里数据库存的图片字段是img
                    if ($key != 'img') {
                        $objActSheet->setCellValue("$letter[$j]$i", $value);
                    }
                    //是图片是加入图片到excel
                    if ($key == 'img') {
                        if ($value != '') {
                            $value = iconv("UTF-8", "GB2312", $value); //防止中文命名的文件
                            // 图片生成
                            $objDrawing[$key] = new \PHPExcel_Worksheet_Drawing();
                            // 图片地址
                            $objDrawing[$key]->setPath('.\Uploads' . $value);
                            // 设置图片宽度高度
                            $objDrawing[$key]->setHeight('80px'); //照片高度
                            $objDrawing[$key]->setWidth('80px'); //照片宽度
                            // 设置图片要插入的单元格
                            $objDrawing[$key]->setCoordinates('D' . $i);
                            // 图片偏移距离
                            $objDrawing[$key]->setOffsetX(12);
                            $objDrawing[$key]->setOffsetY(12);
                            //下边两行图片单元格的格式有作用
                            //$objDrawing[$key]->getShadow()->setVisible(true);
                            //$objDrawing[$key]->getShadow()->setDirection(50);
                            $objDrawing[$key]->setWorksheet($objActSheet);
                        }
                    }
                    $j++;
                }
                //设置单元格高度，暂时没有找到统一设置高度方法
                $objActSheet->getRowDimension($i)->setRowHeight('80px');
            }
        }
        // 带下拉框
        if ($isselect) {
            // 查询班级信息
            $class     = new Classes;
            $classInfo = $class->field('class_id,class_name')->where('status', 2)->select();
            // type为1为用户信息模板
            if ($type == 1) {
                // grade年级信息
                $ary1      = array('id' => '1', 'name' => '幼儿园');
                $ary2      = array('id' => '2', 'name' => '小学');
                $ary2      = array('id' => '3', 'name' => '其它');
                $gradeData = [$ary1, $ary2];
                // 数组重组
                foreach ($gradeData as $key => $value) {
                    $list[] = $value['name'];
                }
                // 定义下拉框 C列
                foreach ($gradeData as $k => $v) {
                    /*设置下拉*/
                    $str            = implode(',', $list);
                    $objValidation1 = $excel->getActiveSheet()->getCell('C' . ($k + 2))->getDataValidation(); //从第二行开始有下拉样式
                    $objValidation1->setType(\PHPExcel_Cell_DataValidation::TYPE_LIST)
                        ->setErrorStyle(\PHPExcel_Cell_DataValidation::STYLE_INFORMATION)
                        ->setAllowBlank(false)
                        ->setShowInputMessage(true)
                        ->setShowErrorMessage(true)
                        ->setShowDropDown(true)
                        ->setErrorTitle('输入的值有误')
                        ->setError('您输入的值不在下拉框列表内.')
                    // ->setPromptTitle('')
                        ->setPrompt('')
                        ->setFormula1('"' . $str . '"');
                };
                // 非空判断
                if ($classInfo && count($classInfo) != 0) {
                    // 数组重组
                    foreach ($classInfo as $key => $value) {
                        $list4[] = $value['class_name'];
                    }
                    // 定义下拉框 D列
                    foreach ($classInfo as $k => $v) {
                        /*设置下拉*/
                        $str4           = implode(',', $list4);
                        $objValidation4 = $excel->getActiveSheet()->getCell('D' . ($k + 2))->getDataValidation(); //从第二行开始有下拉样式
                        $objValidation4->setType(\PHPExcel_Cell_DataValidation::TYPE_LIST)
                            ->setErrorStyle(\PHPExcel_Cell_DataValidation::STYLE_INFORMATION)
                            ->setAllowBlank(false)
                            ->setShowInputMessage(true)
                            ->setShowErrorMessage(true)
                            ->setShowDropDown(true)
                            ->setErrorTitle('输入的值有误')
                            ->setError('您输入的值不在下拉框列表内.')
                        // ->setPromptTitle('')
                            ->setPrompt('')
                            ->setFormula1('"' . $str4 . '"');
                    };
                }
                // 性别信息
                $ary3       = array('id' => '1', 'name' => '男');
                $ary4       = array('id' => '0', 'name' => '女');
                $genderData = [$ary3, $ary4];
                // 数组重组
                foreach ($genderData as $key => $value) {
                    $list2[] = $value['name'];
                }
                // 查询班级信息
                // $class = new Classes;
                // $classInfo  = $class->field('class_id,class_name')->where('status', 2)->select();
                // 定义下拉框 E列
                foreach ($genderData as $k => $v) {
                    /*设置下拉*/
                    $str2           = implode(',', $list2);
                    $objValidation2 = $excel->getActiveSheet()->getCell('E' . ($k + 2))->getDataValidation(); //从第二行开始有下拉样式
                    $objValidation2->setType(\PHPExcel_Cell_DataValidation::TYPE_LIST)
                        ->setErrorStyle(\PHPExcel_Cell_DataValidation::STYLE_INFORMATION)
                        ->setAllowBlank(false)
                        ->setShowInputMessage(true)
                        ->setShowErrorMessage(true)
                        ->setShowDropDown(true)
                        ->setErrorTitle('输入的值有误')
                        ->setError('您输入的值不在下拉框列表内.')
                    // ->setPromptTitle('')
                        ->setPrompt('')
                        ->setFormula1('"' . $str2 . '"');
                };
                //单独设置H列宽度为15
                $objActSheet->getColumnDimension('H')->setWidth(15);
                $objActSheet->getColumnDimension('I')->setWidth(15);
            }
            // type为2为教师信息模板
            if ($type == 2) {
                // 性别信息
                $ary3       = array('id' => '0', 'name' => '女');
                $ary4       = array('id' => '1', 'name' => '男');
                $genderData = [$ary3, $ary4];
                // 数组重组
                foreach ($genderData as $key => $value) {
                    $list2[] = $value['name'];
                }
                // 定义下拉框 B列
                foreach ($genderData as $k => $v) {
                    /*设置下拉*/
                    $str2           = implode(',', $list2);
                    $objValidation2 = $excel->getActiveSheet()->getCell('B' . ($k + 2))->getDataValidation(); //从第二行开始有下拉样式
                    $objValidation2->setType(\PHPExcel_Cell_DataValidation::TYPE_LIST)
                        ->setErrorStyle(\PHPExcel_Cell_DataValidation::STYLE_INFORMATION)
                        ->setAllowBlank(false)
                        ->setShowInputMessage(true)
                        ->setShowErrorMessage(true)
                        ->setShowDropDown(true)
                        ->setErrorTitle('输入的值有误')
                        ->setError('您输入的值不在下拉框列表内.')
                    // ->setPromptTitle('')
                        ->setPrompt('')
                        ->setFormula1('"' . $str2 . '"');
                };
            }
        }
        // 文件重命名,避免中文乱码
        $savefile = iconv("utf-8", "gb2312", $savefile);
        //清除缓冲区,避免乱码
        ob_end_clean();

        // header('Content-Type: application/vnd.ms-excel');
        // // 下载的excel文件名称，Excel5后缀为xls
        // header('Content-Disposition: attachment;filename="' . $savefile . '.xlsx"');
        // header('Cache-Control: max-age=0');

        // IE9
        // header('Cache-Control: max-age=1');
        // 用户下载excel
        // $objWriter = \PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        // dump($objWriter);die;
        // $objWriter ->save('php://output');

        // 保存excel在服务器上
        $objWriter = new \PHPExcel_Writer_Excel2007($excel);
        $objWriter->save(PUBLIC_PATH . 'static' . DS . "excel" . DS . 'export' . DS . $savefile . '.xlsx');

        $baseUrl      = "https://art.up.maikoo.cn/static";
        $downloadPath = $baseUrl . DS . "excel" . DS . 'export' . DS . $savefile . '.xlsx';
        // if(file_exists($downloadPath)){
        //     readfile($downloadPath);
        // }

        return $downloadPath;
    }

    /**
     * 取出excel中的数据
     * @param  string $filename 文件路径
     * @param  string $exts     文件格式
     * @param  int    $type     导出类型
     * @return array            返回结果
     */
    public function getExcelData($filename, $exts, $type)
    {
        //导入PHPExcel类库
        // import("Org.Util.PHPExcel");
        Loader::import('PHPExcel.PHPExcel'); //必须手动导入，否则会报PHPExcel类找不到
        //不同类型的文件导入不同的类
        if ($exts == 'xls') {
            Loader::import("PHPExcel.PHPExcel.Reader.Excel5");
            $PHPReader = new \PHPExcel_Reader_Excel5();
        } else if ($exts == 'xlsx') {
            Loader::import("PHPExcel.PHPExcel.Reader.Excel2007");
            $PHPReader = new \PHPExcel_Reader_Excel2007();
        }
        //载入文件
        $PHPExcel = $PHPReader->load($filename);
        //获取表中的第一个工作表，如果要获取第二个，把0改为1，依次类推
        $currentSheet = $PHPExcel->getSheet(0);
        //获取总列数
        $allColumn = $currentSheet->getHighestColumn();
        //获取总行数
        $allRow = $currentSheet->getHighestRow();
        //循环获取表中的数据，$currentRow表示当前行，从哪行开始读取数据，索引值从0开始
        for ($currentRow = 1; $currentRow <= $allRow; $currentRow++) {
            //从哪列开始，A表示第一列
            for ($currentColumn = 'A'; $currentColumn <= $allColumn; $currentColumn++) {
                //数据坐标
                $address = $currentColumn . $currentRow;
                //读取到的数据，保存到数组$arr中
                $data[$currentRow][$currentColumn] = $currentSheet->getCell($address)->getValue();
            }
        }
        // @unlink ( $filename ); //删除上传的文件
        // type为1为导入用户信息
        if ($type == 1) {
            $res = $this->importUserData($data);
        }
        // type为2为导入教师信息
        if ($type == 2) {
            $res = $this->importTeacherData($data);
        }
        return $res;
    }

    /**
     * 导入用户的excel数据
     * @param  ary  $data 读取到的excel数据
     * @return ary        导入结果
     */
    public function importUserData($data)
    {
        //检测模版是否标准
        $title = array(
            'A' => '用户名',
            'B' => '学号',
            'C' => '年级',
            'D' => '班级',
            'E' => '性别',
            'F' => '生日',
            'G' => '手机号',
            'H' => '剩余课时',
            'I' => '课程结束时间',
        );
        if ($title != $data[1]) {
            return objReturn(400, '您的模版不正确，请下载标准模版！');
            exit;
        }
        // data不为空
        if (count($data) < 2) {
            return objReturn(400, '导入失败！excel数据不完整！');
            exit;
        }
        // 数据字段非空判断
        foreach ($data as $key => $value) {
            if (empty($value)) {
                return objReturn(400, '导入失败！excel数据不完整！');
                break 1;
                exit;
            }
        }
        // 查询班级信息
        $class     = new Classes;
        $classInfo = $class->field('class_id,class_name')->where('status', 2)->select();
        if (!$classInfo) {
            return objReturn(400, '导入失败！请检查是否添加班级信息！');
            exit;
        }
        $dataList = [];
        // 去掉表头
        unset($data[1]);
        // dump($data);die;
        // 构造数组存入数据库
        foreach ($data as $k => $v) {
            $temp['username'] = $v['A'];
            $temp['stu_no']   = $v['B'];
            $temp['birth']    = $v['F'];
            $temp['phone']    = $v['G'];
            if ($v['C'] == '幼儿园') {
                $temp['grade'] = 1;
            }
            if ($v['C'] == '小学') {
                $temp['grade'] = 2;
            }
            if ($v['C'] == '其它') {
                $temp['grade'] = 3;
            }
            if ($v['E'] == '男') {
                $temp['user_gender'] = 1;
            }
            if ($v['E'] == '女') {
                $temp['user_gender'] = 0;
            }
            $dataList[] = $temp;
        }
        //批量导入数组键名必须从0开始
        $user   = new User;
        $result = $user->saveAll($dataList);
        if ($result) {
            // 查询用户信息
            $user     = new User;
            $userData = $user->field('uid,phone')->where('status', '<>', 3)->select();
            if (!$userData && count($userData) == 0) {
                return objReturn(400, '导入失败！用户信息不存在！');
                exit;
            }
            $dataList2 = [];
            // 数据
            foreach ($data as $k => $v) {
                $temp2['course_left_times'] = $v['H'];
                $temp2['course_end_at']     = strtotime($v['I']);
                $temp2['created_at']        = time();
                $temp2['status']            = 1;
                // 班级的id
                foreach ($classInfo as $key => $value) {
                    if ($v['D'] == $value['class_name']) {
                        $temp2['class_id'] = $value['class_id'];
                    }
                }
                // 通过手机号匹配uid
                foreach ($userData as $ke => $val) {
                    if ($v['G'] == $val['phone']) {
                        $temp2['uid'] = $val['uid'];
                    }
                }
                $dataList2[] = $temp2;
            }
            // dump($dataList2);die;
            // 写入classes_user表数据
            $classes_user = new Classes_user;
            $res          = $classes_user->saveAll($dataList2);
            if ($res) {
                // 写入数据库成功后删除Session
                Session::delete('excelPath');
                return objReturn(0, '导入成功！');
            } else {
                return objReturn(400, '导入失败！');
            }
        } else {
            return objReturn(400, '导入失败！');
        }
    }

    /**
     * 导入教师的excel数据
     * @param  ary  $data 读取到的excel数据
     * @return ary        导入结果
     */
    public function importTeacherData($data)
    {
        //检测模版是否标准
        $title = array(
            'A' => '教师姓名',
            'B' => '性别',
            'C' => '生日',
            'D' => '手机号',
        );
        if ($title != $data[1]) {
            return objReturn(400, '您的模版不正确，请下载标准模版！');
            exit;
        }
        // data不为空
        if (count($data) < 2) {
            return objReturn(400, '导入失败！excel数据不完整！');
            exit;
        }
        // 数据字段非空判断
        foreach ($data as $key => $value) {
            if (empty($value)) {
                return objReturn(400, '导入失败！excel数据不完整！');
                break 1;
                exit;
            }
        }
        $dataList = [];
        // 去掉表头
        unset($data[1]);
        // dump($data);die;
        // 构造数组存入数据库
        foreach ($data as $k => $v) {
            $temp['teacher_name']   = $v['A'];
            $temp['teacher_gender'] = $v['B'];
            $temp['teacher_birth']  = $v['C'];
            $temp['teacher_phone']  = $v['D'];
            if ($v['B'] == '男') {
                $temp['teacher_gender'] = 1;
            }
            if ($v['B'] == '女') {
                $temp['teacher_gender'] = 0;
            }
            $dataList[] = $temp;
        }
        //批量导入数组键名必须从0开始
        $teacher = new Teacher;
        $result  = $teacher->saveAll($dataList);
        // dump($result);die;
        if ($result) {
            // 写入数据库成功后删除Session
            Session::delete('excelPath');
            return objReturn(0, '导入成功！');
        } else {
            return objReturn(400, '导入失败！');
        }
    }
}