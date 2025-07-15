import os
from langchain_google_genai import ChatGoogleGenerativeAI
from pprint import pprint

#if "GOOGLE_API_KEY" not in os.environ:
    #os.environ["GOOGLE_API_KEY"] = getpass.getpass("Enter your Google AI API key: ")
os.environ["GOOGLE_API_KEY"] = 'AIzaSyCRYchKjFliZhvG2EYQqJE1rnNYoD_T-fs'
gemini_flash_chat = ChatGoogleGenerativeAI(model="gemini-2.5-flash", temperature=0)
var = gemini_flash_chat.invoke("")
print(var.content)