from PyPDF2 import PdfReader
import os
'''
File to create classes to handle data entered by the user.
'''
class FSource:
    '''
    Class for one source file
    '''
    def __init__(self, filename, filepath):
        self.filename = filename
        self.filepath = filepath
        self.raw_text = self.extract_text()

    def extract_text(self):
        reader = PdfReader(self.filepath)
        text = ""
        for page in reader.pages:
            content = page.extract_text()
            if content:
                text += content
        return text.strip()
    
    def __str__(self):
        return f"Name : {self.filename} || Path : {self.filepath} || content :\n{self.raw_text}"

class Source:
    '''
    Class for having a dictionary containing all source files
    '''
    def __init__(self, files, option, mod, upload_folder='uploads'):
        self.option = option
        self.mod = mod
        self.upload_folder = upload_folder
        self.f_sources={}
        self._load_files(files)

    def _load_files(self, files):
        os.makedirs(self.upload_folder, exist_ok=True)
        for file in files:
            filename = file.filename  # Keep the original filename
            filepath = os.path.join(self.upload_folder, filename)
            file.save(filepath)
            self.f_sources[filename] = FSource(filename, filepath)

    def to_dict(self):
        return {fname: fsource.raw_text for fname, fsource in self.f_sources.items()}
    def __str__(self):
        return f"Option : {self.option} | Mod ; {self.mod}"+"\n".join(str(item) for item in self.f_sources.keys())+"||".join(str(items) for items in self.f_sources.values())

class FResult:
    '''
    Class File for one result
    '''
    def __init__(self, filename, raw_text, option):
        self.filename = filename
        self.option = option
        self.modified_text = raw_text.replace('\n',' ').strip()
    
    def __str__(self):
        return f"Name : {self.filename} || Content:\n{self.modified_text}"

class Result:
    '''
    Class for a dictionary containing all results
    '''
    def __init__(self, source: Source):
        self.f_results={}
        self._process(source)

    def _process(self, source: Source):
        for fname, fsource in source.f_sources.items():
            self.f_results[fname] = FResult(fname, fsource.raw_text, source.option)

    def to_dict(self):
        return {fname: fresult.modified_text for fname, fresult in self.f_results.items()}
    
    def __str__(self):
        return "\n".join(str(fresult) for fresult in self.f_results.keys())+"||".join(str(items) for items in self.f_results.values())
