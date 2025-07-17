import os
import classes
from dotenv import load_dotenv
from langchain_google_genai import ChatGoogleGenerativeAI

class AI:
    def __init__(self,key = None,mod = "gemini-2.5-flash",temp=0):
        if key is None:
            key = os.environ.get('GOOGLE_API_KEY')
        self.model = mod
        self.temp = temp
        self.llm = None
        if not(self.key_setup()):
            self.model = False
            self.temp = self.model
        else:
            self.llm = ChatGoogleGenerativeAI(model = self.model,temperature = self.temp)
    
    def key_setup(self):
        if "GOOGLE_API_KEY" not in os.environ:
            print("Error in environment key, using .env instead")
            load_dotenv("../.env")
            self.key = os.getenv("GOOGLE_API_KEY")
            print(self.key)
            if type(self.key) != type("str"):
                print("The value in .env file is incorrect, the program will stop")
                return False
        return True

    def invoke_abs(self,sources,type = 0):
        prompt= "You are an expert academic summarizer and synthesis engine. Your task is to create a comprehensive, concise, " \
        "and coherent abstract that synthesizes the key information, main arguments, findings, and conclusions presented " \
        "in the cortex of text provided."

        input_specs="**Input:** You will be provided with an array of string representing the text of all " \
        "source document. Each source text will be clearly delimited by `---SOURCE_START---` and `---SOURCE_END---` and have its index."

        ex_input ="**Example of Input Format in each index** : ---SOURCE_START---[Full text of Source 1]---SOURCE_END---"

        output_specs = "**Output Requirements:** 1**Content:** *Identify the overarching theme, research question, or central topic addressed " \
        "by the collection of sources. *Synthesize the primary arguments, methodologies (if relevant and common across sources), and key " \
        "findings or insights from the sources. *Highlight any significant agreements, disagreements, or different perspectives that emerge " \
        "from the texts. *Conclude with the overall implications, a summary of the collective insights, or the main takeaway from the combined information." \
        "2.**Format & Style:** *The abstract should be a single, coherent paragraph. *Aim for a length of approximately **250-350 words** " \
        "(adjust this range based on the expected complexity and number of your sources). *Maintain an objective, academic, and formal tone. " \
        "*Avoid direct quotes unless absolutely essential for conveying a specific concept, and if so, keep them very brief. *Do not include " \
        "citations or references within the abstract itself. *Do not introduce the sources individually (e.g., 'Source 1 states...', " \
        "'According to the second text...'). Instead, integrate the information seamlessly as if it were from a single, unified study." \
        "*Consider all sources as one single batch of information and do not present each source seperatly" \
        "*Language : Use the same language as the input source (i.e : source in french, write the summary in french)" \
        "3.**Result Format** : The result is to be written with markdown markups to make the text readble and emphasising on important notions. " \
        "The result must be encapsulated by : ---ABSTRACT START--- `abstract` ---ABSTRACT END--- allowing for a easier post-treatment."
         
        if(type ==1): # Documents treated seperatly
            prompt = "You are an expert academic summarizer and synthesis engine. Your task is to create comprehensive, concise, " \
            "and coherent abstracts that synthesizes the key information, main arguments, findings, and conclusions presented " \
            "in each text provided. Treat each source as a signle element unrelated to the others and give an abstract for each one of them."
            output_specs = "**Output Requirements:** 1**Content:** *Identify the overarching theme, research question, or central topic addressed " \
            "of each source. *Synthesize the primary arguments, methodologies (if relevant), and key " \
            "findings or insights from each source. *Highlight any significant agreements, disagreements, or different perspectives that emerge " \
            "from each of the texts. *Conclude with the overall implications, a summary of the collective insights, or the main takeaway for each source." \
            "2.**Format & Style:** *The abstract should be multiple coherent paragraph,one for each source. *Aim for a length of approximately **250-350 words** " \
            "(adjust this range based on the expected complexity and number of your sources). *Maintain an objective, academic, and formal tone. " \
            "*Avoid direct quotes unless absolutely essential for conveying a specific concept, and if so, keep them very brief. *Do not include " \
            "citations or references within the abstract itself. *Do not introduce the sources individually (e.g., 'Source 1 states...', " \
            "'According to the second text...') as it is repetitive, considering you analyse and summarise each source independantly." \
            "*Language : Use the same language as the input source (i.e : source in french, write the summary in french)" \
            "3.**Result Format** : The result is to be written with markdown markups to make the text readble and emphasising on important notions. " \
            "Each source must have its abstract which must be encapsulated by : ---ABSTRACT START--- `abstract` ---ABSTRACT END--- allowing for a easier post-treatment."

            ex_output = "**Exemple of Output Format : ---ABSTRACT START--- [abstract of Source 1] ---ABSTRACT END--- ---ABSTRACT START--- [abstract of Source 2] ---ABSTRACT END---"

            result = self.llm.invoke(f"{prompt}\n{input_specs}\n{ex_input}\n{output_specs}\n{ex_output}\nSources : {sources}")
            return result.content
        result = self.llm.invoke(f"{prompt}\n{input_specs}\n{ex_input}\n{output_specs}\nSources : {sources}")
        return result.content
        

        
    def invoke_quiz(self,sources,type = 0):
        prompt = "You are an expert academic engine specialised in quiz creation. Generate a multiple-choice quiz " \
        "based on the following array of strings, in which each index is the text of a source, while using the same language as the sources."

        input_specs="**Input:** You will be provided with an array of string representing the text of all " \
        "source document. Each source text will be clearly delimited by `---SOURCE_START---` and `---SOURCE_END---` and have its index." \
        "Exemple : ---SOURCE_START---[Full text of Source 1]---SOURCE_END---"

        quiz_param = f"**Quiz Parameters** : 1. Number of questions : between 5 and 50 depending on the need." \
        f"2. Proposition per Questions : Between 2 and 4 depending on the need of the question. " \
        f"3. Distractor Quality : Ensure incorrect options (distractors) are plausible but clearly incorrect." \
        "They should ideally be derived from the source material (e.g., misinterpretations, details from other " \
        "parts of the text) or common misconceptions related to the topic, rather than obviously wrong or irrelevant." \
        "4. Question Types : Vary the question types to test different aspects of understanding (e.g., factual recall, " \
        "inferential reasoning, understanding of key concepts, identification of main ideas, vocabulary)." \
        "5. Tone : Maintain a clear, concise, and objective tone." 

        output_format="**Output Format:** The output must be format that can be saved directly as a json file like the following :"
        ex_json_file=""
        current_directory = os.path.dirname(os.path.abspath(__file__))
        with open(f"{current_directory}/ex.json", "r", encoding="utf-8") as f:
            ex_json_file = f.read()

        if type == 1:
            add = "Create a quiz for each sources."
            result = self.llm.invoke(f"{prompt}\n{add}\n{input_specs}\n{sources}\n{quiz_param}\n{output_format}\n{ex_json_file}")
            return result.content
        result = self.llm.invoke(f"{prompt}\n{input_specs}\n{sources}\n{quiz_param}\n{output_format}\n{ex_json_file}")
        return result.content
        

def translate(choice):
    if choice == "sngl":# All documents are treated seperatly
        return 1
    else:# One document for all
        return 0
