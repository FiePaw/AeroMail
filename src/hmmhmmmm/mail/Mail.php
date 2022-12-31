<?php

namespace hmmhmmmm\mail;

use hmmhmmmm\mail\cmd\MailCommand;
use hmmhmmmm\mail\data\Language;
use hmmhmmmm\mail\data\PlayerData;
use hmmhmmmm\mail\listener\EventListener;
use hmmhmmmm\mail\scheduler\MailTask;
use hmmhmmmm\mail\ui\Form;
use jojoe77777\FormAPI\Form as jojoe77777Form;



use pocketmine\player;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\Plugin;
use pocketmine\utils\Config;

class Mail extends PluginBase implements MailAPI{
   public static Mail $instance;
   private $prefix = "?";
   private $facebook = "§cwithout";
   private $youtube = "§cwithout";
   private $discord = "§cwithout";
   private $language = null;
   private $form = null;
   private $PluginOwned;
   public $array = [];
   
   public $langClass = [
      "thai",
      "english"
   ];
   
    public static function getInstance(): self
    {
        return self::$instance;
    }
   public function onLoad(): void
   {
      self::$instance = $this;
      Mail::$instance = $this;
   } 
   
   public function onEnable(): void
   {
      @mkdir($this->getDataFolder());
      @mkdir($this->getDataFolder()."account/");
      @mkdir($this->getDataFolder()."language/");
      $this->saveDefaultConfig();
      $this->prefix = "Mail";
      $this->facebook = "https://bit.ly/39ULjqk";
      $this->youtube = "https://bit.ly/2HL1j28";
      $this->discord = "https://discord.gg/n6CmNr";
      $this->form = new Form($this);
      $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
      $this->getScheduler()->scheduleRepeatingTask(new MailTask($this), 20);
      $this->getServer()->getCommandMap()->register("mail", new MailCommand($this));
      
      $langConfig = $this->getConfig()->getNested("language");
      if(!in_array($langConfig, $this->langClass)){
         $this->getLogger()->error("§cNot found language ".$langConfig.", Please try ".implode(", ", $this->langClass));
         $this->getServer()->getPluginManager()->disablePlugin($this);
      }else{
         $this->language = new Language($this, $langConfig);
      }
      if(!class_exists(jojoe77777Form::class)){
         $this->getLogger()->error($this->language->getTranslate("notfound.libraries", ["FormAPI"]));
         $this->getServer()->getPluginManager()->disablePlugin($this);
         return;
      }
   }
   
   public function getPrefix(): string{
      return "§e[§d".$this->prefix."§e]§f";
   }
   public function getFacebook(): string{
      return $this->facebook;
   }
   public function getYoutube(): string{
      return $this->youtube;
   }
   public function getDiscord(): string{
      return $this->discord;
   }
   public function getLanguage(): Language{
      return $this->language;
   }
   public function getForm(): Form{
      return $this->form;
   }
   public function getPlayerData(string $name): PlayerData{
      return new PlayerData($this, $name);
   }
   public function getPluginInfo(): string{
      $author = implode(", ", $this->getDescription()->getAuthors());
      $arrayText = [
         $this->getPrefix()." ".$this->getLanguage()->getTranslate("plugininfo.name", [$this->getDescription()->getName()]),
         $this->getPrefix()." ".$this->getLanguage()->getTranslate("plugininfo.version", [$this->getDescription()->getVersion()]),
         $this->getPrefix()." ".$this->getLanguage()->getTranslate("plugininfo.author", [$author]),
         $this->getPrefix()." ".$this->getLanguage()->getTranslate("plugininfo.description"),
         $this->getPrefix()." ".$this->getLanguage()->getTranslate("plugininfo.facebook", [$this->getFacebook()]),
         $this->getPrefix()." ".$this->getLanguage()->getTranslate("plugininfo.youtube", [$this->getYoutube()]),
         $this->getPrefix()." ".$this->getLanguage()->getTranslate("plugininfo.website", [$this->getDescription()->getWebsite()]),
         $this->getPrefix()." ".$this->getLanguage()->getTranslate("plugininfo.discord", [$this->getDiscord()]),
      ];
      return implode("\n", $arrayText);
   }
   public function isMail(string $name): bool{
      $playerData = $this->getPlayerData($name);
      $data = $playerData->getConfig()->getAll();       
      return isset($data["mail"]);        
   }
   public function getMailSenderCount(string $name): int{
      $playerData = $this->getPlayerData($name);
      $data = $playerData->getConfig()->getAll();
      return count($data["mail"]["message"]);
   }
   public function getMailSender(string $name): array{
      $playerData = $this->getPlayerData($name);
      $data = $playerData->getConfig()->getAll();
      return array_keys($data["mail"]["message"]);
   }
   public function isMailSender(string $name, string $senderName): bool{
      $playerData = $this->getPlayerData($name);
      $data = $playerData->getConfig()->getAll();       
      return isset($data["mail"]["message"][$senderName]);
   }
   
   public function setCountMail(string $name, int $count): void{
      $playerData = $this->getPlayerData($name);
      $data = $playerData->getConfig();      
      $data->setNested("mail.count", $count);
      $data->save();
   }
   public function getCountMail(string $name): int{
      $playerData = $this->getPlayerData($name);
      $data = $playerData->getConfig()->getAll();      
      return $data["mail"]["count"];
   }
   public function isCountMailSender(string $name, string $senderName): bool{
      $playerData = $this->getPlayerData($name);
      $data = $playerData->getConfig()->getAll();       
      return isset($data["mail"]["message"][$senderName]["count"]);        
   }     
   public function setCountMailSender(string $name, string $senderName, int $count): void{
      $playerData = $this->getPlayerData($name);
      $data = $playerData->getConfig();      
      $data->setNested("mail.message.".$senderName.".count", $count);
      $data->save();
   }
   public function getCountMailSender(string $name, string $senderName): int{
      $playerData = $this->getPlayerData($name);
      $data = $playerData->getConfig()->getAll();       
      return $data["mail"]["message"][$senderName]["count"];
   }
   public function getCountMailSenderWrite(string $name, string $senderName): int{
      $playerData = $this->getPlayerData($name);
      $data = $playerData->getConfig()->getAll();
      return count($data["mail"]["message"][$senderName]["write"]);
   }
   public function getMailSenderWrite(string $name, string $senderName): array{
      $playerData = $this->getPlayerData($name);
      $data = $playerData->getConfig()->getAll();
      return array_keys($data["mail"]["message"][$senderName]["write"]);     
   }
   public function isCountMailSenderWrite(string $name, string $senderName, int $msgCount): bool{
      $playerData = $this->getPlayerData($name);
      $data = $playerData->getConfig()->getAll();
      return isset($data["mail"]["message"][$senderName]["write"][$msgCount]);
   }     
   public function getMailRead(string $name, string $senderName, int $msgCount): string{
      $playerData = $this->getPlayerData($name);
      $data = $playerData->getConfig()->getAll();       
      return $data["mail"]["message"][$senderName]["write"][$msgCount]["read"];
   }
   public function setMailRead(string $name, string $senderName, int $msgCount, bool $read = false): void{
      $playerData = $this->getPlayerData($name);
      $data = $playerData->getConfig();
      if($read){
         if($this->getLanguage()->getLang() == "thai"){
            $data->setNested("mail.message.".$senderName.".write.".$msgCount.".read", "§aอ่านแล้ว");
         }
         if($this->getLanguage()->getLang() == "english"){
            $data->setNested("mail.message.".$senderName.".write.".$msgCount.".read", "§aRead already");
         }
      }else{
         if($this->getLanguage()->getLang() == "thai"){
            $data->setNested("mail.message.".$senderName.".write.".$msgCount.".read", "§cยังไม่อ่าน");
         }
         if($this->getLanguage()->getLang() == "english"){
            $data->setNested("mail.message.".$senderName.".write.".$msgCount.".read", "§cNot yet read");
         }
      }
      $data->save();
   }
   public function getMailMsg(string $name, string $senderName, int $msgCount): string{
      $playerData = $this->getPlayerData($name);
      $data = $playerData->getConfig()->getAll();       
      return $data["mail"]["message"][$senderName]["write"][$msgCount]["msg"];
   }
   public function setMailMsg(string $name, string $senderName, int $msgCount, string $message): void{
      $playerData = $this->getPlayerData($name);
      $data = $playerData->getConfig();       
      $data->setNested("mail.message.".$senderName.".write.".$msgCount.".msg", $message);
      $data->save();
   }
   public function getCountMailPlayers(string $name): int{
      $playerData = $this->getPlayerData($name);
      $data = $playerData->getConfig()->getAll();
      return count($data["mail"]["players"]);
   }
   public function getMailPlayers(string $name): array{
      $playerData = $this->getPlayerData($name);
      $data = $playerData->getConfig()->getAll();
      return $data["mail"]["players"];
   }
   public function setMailPlayers(string $name, string $senderName): void{
      $playerName = strtolower($name);
      $path = $this->getDataFolder()."account/$playerName.yml";
      $config = new Config($path, Config::YAML, array());
      $data = $config->getAll();
      $senderName = strtolower($senderName);
      if(!(in_array($senderName, $data["mail"]["players"]))){
         $data["mail"]["players"][] = $senderName;
      }
      $config->setAll($data);
      $config->save();
   }
   public function addMail(string $name, Player $sender, string $message, bool $tip = true): void{
      $playerData = $this->getPlayerData($name);
      $data = $playerData->getConfig()->getAll();       
      $senderName = strtolower($sender->getName());
      if(!$sender instanceof Player){
         $groupSender = "§4Owner";
      }else{
         if($sender->isOp()){
            $groupSender = "§2Admin";
         }else{
            $groupSender = "§ePlayer";
         }
      }
      $message1 = $this->getLanguage()->getTranslate("addmail.message1", [$groupSender, $senderName, date("d/m/Y H:i:s"), $message]);
      if($this->isMailSender($name, $senderName)){
         $msgWrite = $this->getMailSenderWrite($name, $senderName); 
         $msgCount = $this->getCountMailSenderWrite($name, $senderName) + 1;
         for($i = 0; $i < $this->getCountMailSenderWrite($name, $senderName); $i++){//อันนี้แม่งง่ายแต่คิดอยู่นาน
            if($this->isCountMailSenderWrite($name, $senderName, $msgCount)){
               $msgCount++;
            }
         }
      }else{
         $msgCount = 1;
      }
      if($this->isCountMailSender($name, $senderName)){
         $count = $this->getCountMailSender($name, $senderName) + 1;
      }else{
         $count = 1;
      }
      $this->setCountMailSender($name, $senderName, $count);
      $this->setMailRead($name, $senderName, $msgCount, false);
      $this->setMailMsg($name, $senderName, $msgCount, $message1);
      $count = $this->getCountMail($name) + 1;
      $this->setCountMail($name, $count);
      $this->setMailPlayers($senderName, $name);
      $player = $this->getServer()->getPlayer($name); 
      $sender->sendMessage($this->getPrefix()." ".$this->getLanguage()->getTranslate("addmail.message2", [$name, $message]));
      if($player instanceof Player){
         if($tip){
            $player->sendMessage($this->getPrefix()." ".$this->getLanguage()->getTranslate("addmail.message3", [$senderName, $senderName]));
            $player->addTitle(($this->getLanguage()->getTranslate("addmail.titleline1")), ($this->getLanguage()->getTranslate("addmail.titleline2", [$senderName])));
         }
      }
   }     
   public function readMail(string $name, string $senderName, int $msgCount): string{
      return $this->getLanguage()->getTranslate("readmail", [$msgCount, $this->getMailRead($name, $senderName, $msgCount), $this->getMailMsg($name, $senderName, $msgCount)]);
   }
   public function listMail(string $name, string $senderName): string{
      return $this->getLanguage()->getTranslate("listmail", [$senderName, $this->getCountMailSender($name, $senderName)]);
   }
   public function countMail(string $name): string{
      return $this->getPrefix()." ".$this->getLanguage()->getTranslate("countmail", [$this->getCountMail($name)]);
   }
   public function removeCountMailSender(string $name, string $senderName): void{
      if(!($this->getCountMailSender($name, $senderName) == 0)){
         $count = $this->getCountMailSender($name, $senderName) - 1;
         $this->setCountMailSender($name, $senderName, $count);
         $count = $this->getCountMail($name) - 1;
         $this->setCountMail($name, $count);
      }
   }
   public function removeMailSender(string $name, string $senderName, int $msgCount): void{
      $playerName = strtolower($name);
      $path = $this->getDataFolder()."account/$playerName.yml";
      $config = new Config($path, Config::YAML, array());
      $data = $config->getAll();       
      unset($data["mail"]["message"][$senderName]["write"][$msgCount]);
      $config->setAll($data);       
      $config->save();       
   }
   public function delMailSender(Player $player, string $senderName, int $msgCount): void{
      if(!$this->isMailSender($player->getName(), $senderName)){
         $player->sendMessage($this->getPrefix()." ".$this->getLanguage()->getTranslate("mail.command.clear.error3", [$senderName]));
         return;
      }
      if(!$this->isCountMailSenderWrite($player->getName(), $senderName, $msgCount)){
         $player->sendMessage($this->getPrefix()." ".$this->getLanguage()->getTranslate("mail.command.clear.error4", [$msgCount]));
         return;
      }
      $this->removeCountMailSender($player->getName(), $senderName);
      $this->removeMailSender($player->getName(), $senderName, $msgCount);
      $player->sendMessage($this->getPrefix()." ".$this->getLanguage()->getTranslate("mail.command.clear.complete", [$senderName, $msgCount]));
   }
   public function resetMail(string $name): void{
      $playerData = $this->getPlayerData($name);
      $data = $playerData->getConfig();       
      $data->setNested("mail.count", 0);
      $data->setNested("mail.message", []);
      $data->save();
   }
   public function getPrefix(): string{
      return $this->getPlugin()->getPrefix();
   }
   
   public function createCustomForm(?callable $function = null): CustomForm{
      return new CustomForm($function);
   }
   public function createSimpleForm(?callable $function = null): SimpleForm{
      return new SimpleForm($function);
   }
   public function createModalForm(?callable $function = null): ModalForm{
      return new ModalForm($function);
   }

   public function MailMenu(Player $player, string $content = ""): void{
      $form = $this->createSimpleForm(function ($player, $data){
         if(!($data === null)){
            if($data == 0){
               if(!$player->hasPermission("mail.command.write")){
                  $text = $this->getPlugin()->getLanguage()->getTranslate("form.menu.error1");
                  $this->MailMenu($player, $text);
                  return;
               }
               $this->MailWrite($player);
            }
            if($data == 1){
               if(!$player->hasPermission("mail.command.see")){
                  $text = $this->getPlugin()->getLanguage()->getTranslate("form.menu.error1");
                  $this->MailMenu($player, $text);
                  return;
               }
               if($this->getPlugin()->getCountMailPlayers($player->getName()) == 0){
                  $text = $this->getPlugin()->getLanguage()->getTranslate("form.menu.error2");
                  $this->MailMenu($player, $text);
                  return;
               }
               $this->MailSeeAll($player);
            }
            if($data == 2){
               if(!$player->hasPermission("mail.command.read") && !$player->hasPermission("mail.command.readall")){
                  $text = $this->getPlugin()->getLanguage()->getTranslate("form.menu.error1");
                  $this->MailMenu($player, $text);
                  return;
               }
               if($this->getPlugin()->getMailSenderCount($player->getName()) == 0){
                  $text = $this->getPlugin()->getLanguage()->getTranslate("form.menu.error3");
                  $this->MailMenu($player, $text);
                  return;
               }
               $this->MailReadAll($player);
            }
            if($data == 3){
               if(!$player->hasPermission("mail.command.clearall")){
                  $text = $this->getPlugin()->getLanguage()->getTranslate("form.menu.error1");
                  $this->MailMenu($player, $text);
                  return;
               }
               $this->MailClearAll($player);
            }
         }
      });
      $form->setTitle($this->getPrefix()." Menu");
      $form->setContent($content);
      $form->addButton($this->getPlugin()->getLanguage()->getTranslate("form.menu.button1"));
      $form->addButton($this->getPlugin()->getLanguage()->getTranslate("form.menu.button2"));
      $form->addButton($this->getPlugin()->getLanguage()->getTranslate("form.menu.button3", [$this->getPlugin()->getCountMail($player->getName())]));
      $form->addButton($this->getPlugin()->getLanguage()->getTranslate("form.menu.button4"));
      $form->sendToPlayer($player);
   }
   public function MailWrite(Player $player, string $content = ""): void{
      $form = $this->createCustomForm(function ($player, $data){
         if($data == null){
            return;
         }
         $name = explode(" ", $data[1]); 
         if($name[0] == null){
            $text = $this->getPlugin()->getLanguage()->getTranslate("form.write.error1");
            $this->MailWrite($player, $text);
            return;
         }
         $playerData = $this->getPlugin()->getPlayerData($name[0]);
         if(!$playerData->isData()){
            $text = $this->getPlugin()->getLanguage()->getTranslate("form.write.error2");
            $this->MailWrite($player, $text);
            return;
         }
         $message = explode(" ", $data[2]); 
         if($message[0] == null){
            $text = $this->getPlugin()->getLanguage()->getTranslate("form.write.error3");
            $this->MailWrite($player, $text);
            return;
         }
         $message = $data[2];
         $this->getPlugin()->addMail($playerData->getName(), $player, $message, false);
         $pOnline = $this->getPlugin()->getServer()->getPlayer($name[0]);
         if($pOnline instanceof Player){
            $this->MailAdd($pOnline, $player->getName());
         }
      });
      $form->setTitle($this->getPrefix()." Write");
      $form->addLabel($content);
      $form->addInput($this->getPlugin()->getLanguage()->getTranslate("form.write.input1"));
      $form->addInput($this->getPlugin()->getLanguage()->getTranslate("form.write.input2"));
      $form->sendToPlayer($player);
   }
   public function MailSeeMsg(Player $player, string $senderName, string $content = ""): void{
      $senderName = strtolower($senderName);
      $pOnline = $this->getPlugin()->getServer()->getPlayer($senderName);
      $form = $this->createCustomForm(function ($player, $data) use ($senderName, $content, $pOnline){
         if(!($data === null)){
            $message = explode(" ", $data[1]); 
            if($message[0] == null){
               $text = $this->getPlugin()->getLanguage()->getTranslate("form.seemsg.error1")."\n".$content;
               $this->MailSeeMsg($player, $senderName, $text);
               return;
            }
            $message = $data[1];
            $this->getPlugin()->addMail($senderName, $player, $message, false);
            if($pOnline instanceof Player){
               $this->MailAdd($pOnline, $player->getName());
            }
         }
         
      });
      if($pOnline instanceof Player){
         $online = $this->getPlugin()->getLanguage()->getTranslate("form.seemsg.online");
      }else{
         $online = $this->getPlugin()->getLanguage()->getTranslate("form.seemsg.offline");
      }
      $form->setTitle($this->getPlugin()->getLanguage()->getTranslate("form.seemsg.title", [$senderName, $online]));
      $form->addLabel($content);
      $form->addInput("", $this->getPlugin()->getLanguage()->getTranslate("form.seemsg.input1"));
      $form->sendToPlayer($player);
   }
   public function MailSeeAll(Player $player, string $content = ""): void{
       $array = [];
       foreach($this->getPlugin()->getMailPlayers($player->getName()) as $senderName){
         $array[] = $senderName;
      }
      $form = $this->createSimpleForm(function ($player, $data) use ($array){
         if(!($data === null)){
            $name = $array[$data];
            if(!$this->getPlugin()->isMailSender($name, strtolower($player->getName()))){
               $text = $this->getPlugin()->getLanguage()->getTranslate("form.seeall.error1");
               $this->MailSeeAll($player, $text);
               return;
            }
            $array2 = [];
            foreach($this->getPlugin()->getMailSenderWrite($name, strtolower($player->getName())) as $msgCount2){
               $array2[] = $this->getPlugin()->readMail($name, strtolower($player->getName()), $msgCount2);
            }
            if(!empty($array2)){
               $msg = implode("\n", $array2);
               $this->MailSeeMsg($player, $name, $msg);
            }else{
               $player->sendMessage("§cMessage not found");
            }
         }
      });
      $form->setTitle($this->getPrefix()." SeeAll");
      $form->setContent($content);
      for($i = 0; $i < count($array); $i++){
         $form->addButton($array[$i]);
      }
      $form->sendToPlayer($player);
   }
   public function MailReadMsg(Player $player, string $senderName, string $content = ""): void{
      $senderName = strtolower($senderName);
      $pOnline = $this->getPlugin()->getServer()->getPlayer($senderName);
      $form = $this->createCustomForm(function ($player, $data) use ($senderName, $content, $pOnline){
         if(!($data === null)){
            if($data[1] == 0){
               $message = explode(" ", $data[2]); 
               if($message[0] == null){
                  $text = $this->getPlugin()->getLanguage()->getTranslate("form.readmsg.error1")."\n".$content;
                  $this->MailReadMsg($player, $senderName, $text);
                  return;
               }
               $message = $data[2];
               $this->getPlugin()->addMail($senderName, $player, $message, false);
               if($pOnline instanceof Player){
                  $this->MailAdd($pOnline, $player->getName());
               }
            }
            if($data[1] == 1){
               $msgCount = explode(" ", $data[2]); 
               if($msgCount[0] == null && !is_numeric($msgCount[0])){
                  $text = $this->getPlugin()->getLanguage()->getTranslate("form.readmsg.error2")."\n".$content;
                  $this->MailReadMsg($player, $senderName, $text);
                  return;
               }
               $msgCount = (int) $data[2];
               $this->getPlugin()->delMailSender($player, strtolower($senderName), $msgCount);
            }
         }
      });
      if($pOnline instanceof Player){
         $online = $this->getPlugin()->getLanguage()->getTranslate("form.readmsg.online");
         $pOnline->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("form.readmsg.complete", [$player->getName()]));
      }else{
         $online = $this->getPlugin()->getLanguage()->getTranslate("form.readmsg.offline");
      }
      $form->setTitle($this->getPlugin()->getLanguage()->getTranslate("form.readmsg.title", [$senderName, $online]));
      $form->addLabel($content);
      $form->addDropdown($this->getPlugin()->getLanguage()->getTranslate("form.readmsg.dropdown1.title"), [$this->getPlugin()->getLanguage()->getTranslate("form.readmsg.dropdown1.step1"), $this->getPlugin()->getLanguage()->getTranslate("form.readmsg.dropdown1.step2")]); 
      $form->addInput("");
      $form->sendToPlayer($player);
   }
   public function MailReadAll(Player $player, string $content = ""){
       $array = [];
       foreach($this->getPlugin()->getMailSender($player->getName()) as $senderName){
         $array[] = $senderName;
      }
      $form = $this->createSimpleForm(function ($player, $data) use ($array){
         if(!($data === null)){
            $name = $array[$data];
            $count = $this->getPlugin()->getCountMail($player->getName()) - $this->getPlugin()->getCountMailSender($player->getName(), $name);
            $this->getPlugin()->setCountMail($player->getName(), $count);
            $this->getPlugin()->setCountMailSender($player->getName(), $name, 0);
            foreach($this->getPlugin()->getMailSenderWrite($player->getName(), $name) as $msgCount2){
               $this->getPlugin()->setMailRead($player->getName(), $name, $msgCount2, true);
            }
            $array2 = [];
            foreach($this->getPlugin()->getMailSenderWrite($player->getName(), $name) as $msgCount2){
               $array2[] = $this->getPlugin()->readMail($player->getName(), $name, $msgCount2);
            }
            if(!empty($array2)){
               $msg = implode("\n", $array2);
               $this->MailReadMsg($player, $name, $msg);
            }else{
               $player->sendMessage("§cMessage not found");
            }
            
         }
      });
      $form->setTitle($this->getPrefix()." ReadAll");
      $form->setContent($content);         
      for($i = 0; $i < count($array); $i++){
         $form->addButton($this->getPlugin()->listMail($player->getName(), $array[$i]));
      }
      $form->sendToPlayer($player);
   }
   public function MailClearAll(Player $player): void{
      $form = $this->createModalForm(function ($player, $data){
         if(!($data === null)){
            if($data == 1){//ปุ่ม1
               $this->getPlugin()->resetMail($player->getName());
               $player->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("form.clearall.complete"));
            }
            if($data == 0){//ปุ่ม2
            }
         }
      });
      $form->setTitle($this->getPrefix()." ClearAll");
      $text = $this->getPlugin()->getLanguage()->getTranslate("form.clearall.content");
      $form->setContent($text);
      $form->setButton1($this->getPlugin()->getLanguage()->getTranslate("form.clearall.button1")); 
      $form->setButton2($this->getPlugin()->getLanguage()->getTranslate("form.clearall.button2"));
      $form->sendToPlayer($player);
   }
   public function MailAdd(Player $player, string $senderName): void{
      $senderName = strtolower($senderName);
      $sender = $this->getPlugin()->getServer()->getPlayer($senderName);
      if(!$sender instanceof Player){
         $groupSender = "§4Owner";
      }else{
         if($sender->isOp()){
            $groupSender = "§2Admin";
         }else{
            $groupSender = "§ePlayer";
         }
      }
      $form = $this->createModalForm(function ($player, $data) use ($senderName){
         if(!($data === null)){
            if($data == 1){//ปุ่ม1
               $name = $senderName;
               $count = $this->getPlugin()->getCountMail($player->getName()) - $this->getPlugin()->getCountMailSender($player->getName(), $name);
               $this->getPlugin()->setCountMail($player->getName(), $count);
               $this->getPlugin()->setCountMailSender($player->getName(), $name, 0);
               foreach($this->getPlugin()->getMailSenderWrite($player->getName(), $name) as $msgCount2){
                  $this->getPlugin()->setMailRead($player->getName(), $name, $msgCount2, true);
               }
               $array2 = [];
               foreach($this->getPlugin()->getMailSenderWrite($player->getName(), $name) as $msgCount2){
                  $array2[] = $this->getPlugin()->readMail($player->getName(), $name, $msgCount2);
               }
               if(!empty($array2)){
                  $msg = implode("\n", $array2);
                  $this->MailReadMsg($player, $name, $msg);
               }else{
                  $player->sendMessage("§cMessage not found");
               }
            }
            if($data == 0){//ปุ่ม2
            }
         }
      });
      $form->setTitle($this->getPrefix()." Add");
      $text = $this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("form.add.content", [$groupSender, $sender->getName()]);
      $form->setContent($text);
      $form->setButton1($this->getPlugin()->getLanguage()->getTranslate("form.add.button1")); 
      $form->setButton2($this->getPlugin()->getLanguage()->getTranslate("form.add.button2"));
      $form->sendToPlayer($player);
   }
   public function MessageUI(Player $player, string $content = ""): void{
      $form = $this->createSimpleForm(function ($player, $data){
         if($data === null){
            return;
         }
      });
      $form->setTitle($this->getPrefix()." Message UI");
      $form->setContent($content);
      $form->addButton($this->getPlugin()->getLanguage()->getTranslate("form.clearall.button1"));
      $form->sendToPlayer($player);
   }
   
}