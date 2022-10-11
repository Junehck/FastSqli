<?php
header("Content-Type:text/html;charset=utf-8");
error_reporting(E_ALL^E_NOTICE^E_WARNING);

/*数据库配置信息*/
$db_host='localhost';
$db_username='test';
$db_password='';
$db_dbname='test';
$db_port=3306;
/******************/

/*Debug 输出sql语句以及错误信息*/
$debug=0;

/*注入配置信息*/
$error=0;   //报错
$bool=0;    //布尔
$time=0;	//时间
$union=1;   //联合
$stacked=0; //堆叠注入

/*拦截规则(正则)无视大小写*/
$filter="";

/*替换规则,默认将blog.mo60.cn和cn替换为空无视大小写*/
$replace=array('blog.mo60.cn','cn');

/*连接*/
$conn=mysqli_connect($db_host,$db_username,$db_password,$db_dbname,$db_port);
$conn or die("连接错误: " . mysqli_connect_error());

/*判断表是否存在*/
$sql="select count(table_name) as status from information_schema.tables where table_schema='{$db_dbname}' and TABLE_NAME='sqli_data'";  
$result=getRow($sql,$conn);

/*新建数据表并插入数据*/
if($result['status']<1)
{
    $sql="CREATE TABLE `sqli_data` ( `id` INT NOT NULL AUTO_INCREMENT , `title` VARCHAR(32) NOT NULL , `content` TEXT NOT NULL , PRIMARY KEY (`id`)) ENGINE = MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
	$flagid=mt_rand(1111111111,9999999999);
    if(mysqli_query($conn,$sql))
		mysqli_query($conn,"insert into sqli_data(`id`,`title`,`content`) values(1,'女生把90多张卖萌自拍错发班级群','钟同学称，当时和朋友在逛街，看到班级群来了消息准备点进去填表格，刚好朋友又一直在催她发照片，就不小心把照片都发出去了，当时感觉特别崩溃，站在大街上大叫了好几次'),(2,'银比金坚!中国女篮获世界杯亚军','北京时间10月1日，2022女篮世界杯的决赛在悉尼上演，中国女篮61-83不敌美国女篮，获得本届世界杯的亚军。'),(3,'一个博客','YmxvZy5tbzYwLmNu'),({$flagid},'flag','flag{cd441b3ca90ff269074808874ff040b5}')");
}


$id=isset($_GET['id']) ? $_GET['id'] : 1;

/*拦截功能*/
if(!empty($filter) and preg_match("/$filter/is", $id)) {
	exit('hacker!');
}

/*替换规则实现*/
foreach($replace as $k=>$v)
{
	$id=str_ireplace($v,'',$id);
}

$sql="select * from sqli_data where id=$id order by id limit 1";
/*注入方法实现*/
if($error){
	mysqli_query($conn,$sql) or die( mysqli_error($conn));
}elseif($bool){
	echo getRow($sql,$conn) ? 'ok' : 'no';
}elseif($time){
	mysqli_query($conn,$sql);
}elseif($union){
	$result=getRow($sql,$conn);
	echo $result['title'].'<hr>'.$result['content'].'<hr>';
}elseif($stacked){
	$result=getRow_s($sql,$conn);
	echo $result['title'].'<hr>'.$result['content'].'<hr>';
}else{
	echo '未选择注入方式';
}

if($debug)
{
	echo htmlspecialchars($sql).'<hr>';
	echo mysqli_error($conn);
}

function getRow($sql,$conn)
{
    $result=mysqli_query($conn,$sql);
    return mysqli_fetch_assoc($result);
}

function getRow_s($sql,$conn)
{
    if (mysqli_multi_query($conn, $sql))
	{
		if ($result = mysqli_store_result($conn))
		{
			if($row = mysqli_fetch_assoc($result))
			{
				return $row;
			}
		}
	}	
}


?>
