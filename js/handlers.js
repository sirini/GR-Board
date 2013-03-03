/* Demo Note:  This demo uses a FileProgress class that handles the UI for displaying the file name and percent complete.
The FileProgress class is not part of SWFUpload.
*/


/* **********************
   Event Handlers
   These are my custom event handlers to make my
   web application behave the way I went when SWFUpload
   completes different tasks.  These aren't part of the SWFUpload
   package.  They are part of my application.  Without these none
   of the actions SWFUpload makes will show up in my application.
   ********************** */

var COUNT_PLAYER = 1;
var PREV_NO = 1;

// 문자열 치환
function str_replace(str1, str2, str3)
{
	var r = new RegExp(str1, 'g');
	return str3.replace(r, str2);
}

// 올린 파일 다시 삭제하기
function removeFile(id, filename, del, type) {
	
	$.ajax({
		url: 'remove_multi_file.php',
		dataType: 'json',
		type: 'POST',
		data: 'id='+id+'&filename='+filename,
		success: function(json) {
			alert('파일을 삭제하였습니다.');
			$("#grProgressID"+del).fadeOut();
		}
	});
}

function fileQueued(file) {
	try {
		var progress = new FileProgress(file, this.customSettings.progressTarget);
		progress.setStatus("작업중...");
		progress.toggleCancel(true, this);

	} catch (ex) {
		this.debug(ex);
	}
}

function fileQueueError(file, errorCode, message) {
	try {
		if (errorCode === SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED) {
			alert("너무 많은 파일입니다. 조금만 줄여주세요.\n" + (message === 0 ? "업로드 제한치입니다." : "You may select " + (message > 1 ? "up to " + message + " files." : "one file.")));
			return;
		}

		var progress = new FileProgress(file, this.customSettings.progressTarget);
		progress.setError();
		progress.toggleCancel(false);

		switch (errorCode) {
		case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT:
			progress.setStatus("파일이 너무 큽니다. 압축을 통해서 줄여주시거나, 분할압축해 주세요.");
			this.debug("오류: File too big, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
			progress.setStatus("파일크기가 0인 파일은 업로드 할 수 없습니다.");
			this.debug("오류: 파일크기가 0인 업로드, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE:
			progress.setStatus("유효하지 않은 파일형태.");
			this.debug("오류: 유효하지 않은 파일형태, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		default:
			if (file !== null) {
				progress.setStatus("예기치 못한 에러 발생");
			}
			this.debug("오류: " + errorCode + ", File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		}
	} catch (ex) {
        this.debug(ex);
    }
}

function fileDialogComplete(numFilesSelected, numFilesQueued) {
	try {
		if (numFilesSelected > 0) {
			document.getElementById(this.customSettings.cancelButtonId).disabled = false;
		}
		
		/* I want auto start the upload and I can do that here */
		this.startUpload();
	} catch (ex)  {
        this.debug(ex);
	}
}

function uploadStart(file) {
	try {
		/* I don't want to do any file validation or anything,  I'll just update the UI and
		return true to indicate that the upload should start.
		It's important to update the UI here because in Linux no uploadProgress events are called. The best
		we can do is say we are uploading.
		 */
		var progress = new FileProgress(file, this.customSettings.progressTarget);
		progress.setStatus("업로드중입니다...");
		progress.toggleCancel(true, this);
		$('#flashHistory').fadeIn();
	}
	catch (ex) {}
	
	return true;
}

function uploadProgress(file, bytesLoaded, bytesTotal) {
	try {
		var percent = Math.ceil((bytesLoaded / bytesTotal) * 100);

		var progress = new FileProgress(file, this.customSettings.progressTarget);
		progress.setProgress(percent);
		progress.setStatus("업로드중입니다...");
	} catch (ex) {
		this.debug(ex);
	}
}

function uploadSuccess(file, serverData) {
	try {
		var progress = new FileProgress(file, this.customSettings.progressTarget);
		var addMsg = '';
		progress.setComplete();
		var ext = file.name.substring(file.name.lastIndexOf(".") + 1);
		var grFilename = str_replace(' ', '_', serverData).toLowerCase();
		switch (ext.toLowerCase()) {
			case "jpg":
			case "jpeg":
			case "bmp":
			case "gif":
			case "png":
				progress.setStatus('↓ 아래 이미지를 드래그하여 본문 작성폼에 떨어트려 보세요. <span style="cursor: pointer; color: red" title="클릭하시면 이 첨부파일을 다시 삭제합니다." onclick="removeFile(\''+BBS_ID+'\', \''+grFilename+'\', \''+PREV_NO+'\', \'id\');">[삭제하기]</span><br /><img src="data/'+BBS_ID+'/'+grFilename+'" alt="미리보기" class="multi-preview" id="prevID'+PREV_NO+'" />');
				PREV_NO++;
			break;
			case "flv":
			case "aac":
			case "mp3":
			case "mp4":
				progress.setStatus('<span id="pd'+COUNT_PLAYER+'" style="cursor: pointer; color: red" title="클릭하시면 이 첨부파일을 다시 삭제합니다." onclick="removeFile(\''+BBS_ID+'\', \''+grFilename+'\', \''+COUNT_PLAYER+'\', \'pd\');">[아래 동영상 삭제하기]</span><br /><img id="player-box-'+COUNT_PLAYER+'" src="image/player_here.gif" alt="동영상/mp3 플레이어" class="'+grFilename+'" />');
				COUNT_PLAYER++;
			break;
		default:
			//alert(ext.toUpperCase() + " 이미지 파일만 업로드할 수 있습니다.");
			progress.setStatus('업로드를 완료하였습니다. <span style="cursor: pointer; color: red" title="클릭하시면 이 첨부파일을 다시 삭제합니다." onclick="removeFile(\''+BBS_ID+'\', \''+grFilename+'\', \''+PREV_NO+'\', \'id\');">[삭제하기]</span>');
			PREV_NO++;
      }
		progress.toggleCancel(false);

	} catch (ex) {
		this.debug(ex);
	}
}

function uploadError(file, errorCode, message) {
	try {
		var progress = new FileProgress(file, this.customSettings.progressTarget);
		progress.setError();
		progress.toggleCancel(false);

		switch (errorCode) {
		case SWFUpload.UPLOAD_ERROR.HTTP_ERROR:
			progress.setStatus("Upload Error: " + message);
			this.debug("오류: HTTP Error, File name: " + file.name + ", Message: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.UPLOAD_FAILED:
			progress.setStatus("Upload Failed.");
			this.debug("오류: Upload Failed, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.IO_ERROR:
			progress.setStatus("Server (IO) Error");
			this.debug("오류: IO Error, File name: " + file.name + ", Message: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.SECURITY_ERROR:
			progress.setStatus("Security Error");
			this.debug("오류: Security Error, File name: " + file.name + ", Message: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.UPLOAD_LIMIT_EXCEEDED:
			progress.setStatus("Upload limit exceeded.");
			this.debug("오류: Upload Limit Exceeded, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.FILE_VALIDATION_FAILED:
			progress.setStatus("Failed Validation.  Upload skipped.");
			this.debug("오류: File Validation Failed, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.FILE_CANCELLED:
			// If there aren't any files left (they were all cancelled) disable the cancel button
			if (this.getStats().files_queued === 0) {
				document.getElementById(this.customSettings.cancelButtonId).disabled = true;
			}
			progress.setStatus("취소되었습니다.");
			progress.setCancelled();
			break;
		case SWFUpload.UPLOAD_ERROR.UPLOAD_STOPPED:
			progress.setStatus("중지되었습니다.");
			break;
		default:
			progress.setStatus("예기치 못한 에러발생: " + errorCode);
			this.debug("오류: " + errorCode + ", File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		}
	} catch (ex) {
        this.debug(ex);
    }
}

function uploadComplete(file) {
	if (this.getStats().files_queued === 0) {
		document.getElementById(this.customSettings.cancelButtonId).disabled = true;
	}
}

// This event comes from the Queue Plugin
function queueComplete(numFilesUploaded) {
	$('#divStatus').html(numFilesUploaded + " 개의 파일을 업로드 했습니다.");
}